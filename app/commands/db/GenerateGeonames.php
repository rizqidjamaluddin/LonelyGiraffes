<?php

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateGeonames extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lg:db:geonames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate geonames database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        DB::connection()->disableQueryLog();
        $pdo = DB::getPdo();
        $country_list = [];
        $state_list = [];
        $total_countries = 0;
        $total_states = 0;

        $path = __DIR__ . '/../../' . $this->argument('source');
        $filesize = File::size($path);
        $this->info('Importing Geonames database.');
        $this->info("Source File: $path ($filesize bytes)");

        $this->info('> Creating countries list table...');
        $countries = new SplFileObject(__DIR__ . '/../data/general-countries.csv');
        DB::statement('drop table if exists `lookup_countries`');
//        DB::statement(
//            'CREATE TABLE `lookup_countries` (
//                       `id` int UNSIGNED NOT NULL AUTOINCREMENT,
//                       `code` char(2) DEFAULT NULL,
//                       `name` varchar(200) DEFAULT NULL,
//                       PRIMARY KEY (`id`),
//                       KEY `code` (`code`)
//                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
//        );

        Schema::create(
            'lookup_countries',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('code', 2);
                $table->string('name');
                $table->unique('code');
            }
        );

        $this->info('> Importing countries list table...');
        $country_statement = $pdo->prepare('INSERT INTO `lookup_countries` (`code`, `name`) VALUES (:code, :name)');

        // set up formatting helper
        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $countries->seek($countries->getSize());
        $linesTotal = $countries->key();
        $countries->rewind();
        $progress->start($this->output, $linesTotal + 1);

        // insert country data
        while (!$countries->eof()) {
            list($code, $country_name) = $countries->fgetcsv();
            // hotfix for countries with unicode characters
            if ($code == 'AX') {
                $country_name = 'Åland Islands';
            }
            if ($code == 'BL') {
                $country_name = "Saint Barthélemy";
            }
            if ($code == 'CI') {
                $country_name = 'Côte d\'Ivoire';
            }
            if ($code == 'CW') {
                $country_name = 'Curaçao';
            }
            $country_list[$code] = $country_name;
            $country_statement->execute(
                [
                    ':code' => $code,
                    ':name' => $country_name
                ]
            );
            $total_countries++;
            $progress->setCurrent($total_countries);
        }
        $progress->finish();
        $this->info(sprintf("... %116s", "Finished importing countries ($total_countries total)"));


        $this->info('> Creating administration district list table...');
        $states = new SplFileObject(__DIR__ . '/../data/geonames-states.txt');
        DB::statement('drop table if exists `lookup_geoname_states`');

        Schema::create(
            'lookup_geoname_states',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('country_code', 2);
                $table->string('state_code', 20);
                $table->string('population', 20)->nullable();
                $table->string('country', 200)->nullable();
                $table->string('name', 200);
                $table->unique(['country_code', 'state_code']);
            }
        );

        $this->info('> Importing administration district list table...');
        $state_statement = $pdo->prepare(
            'INSERT INTO `lookup_geoname_states` (`country_code`, `state_code`, `name`) VALUES (:country_code, :state_code, :name)'
        );
        while (!$states->eof()) {
            $row = $states->fgets();
            $row = explode("\t", $row);
            list($country_code, $state_code) = explode('.', $row[0]);
            $state_list[$row[0]] = $row[1];
            $state_statement->execute(
                [
                    ':country_code' => $country_code,
                    ':state_code'   => $state_code,
                    ':name'         => $row[1]
                ]
            );
            $total_states++;
        }
        $this->info(sprintf("... %116s", "Finished importing administration districts ($total_states total)"));

        $this->info('> Creating import table...');
        DB::statement('drop table if exists `lookup_geoname_places`');

        Schema::create(
            'lookup_geoname_places',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('geoname_id');
                $table->string('city');
                $table->string('ascii_city');
                $table->decimal('lat', 18, 12);
                $table->decimal('long', 18, 12);
                $table->string('country_code');
                $table->string('state_code');
                $table->string('country');
                $table->string('state');
                $table->string('timezone');
                $table->bigInteger('population');
                $table->index(['lat', 'long']);
                $table->index(['population', 'country', 'state', 'city']);
                $table->index(['city', 'ascii_city']);
            }
        );

        $this->info('> Importing data using PHP import...');

        // DEV NOTES
        // PHP import because the LOAD DATA LOCAL INFILE method was buggy as hell.
        // explode("\t", $file->fgets()) because for whatever reason $file->fgetcsv("\t"), which is supposed to do the
        // same thing, also failed for reasons beyond human comprehension.
        $file = new SplFileObject($path);
        $counter = 0;
        $start = microtime(true);

        $sql = 'INSERT INTO `lookup_geoname_places`
                    (`geoname_id`, `city`, `ascii_city`, `lat`, `long`, `country_code`,`state_code`, `country`, `state`, `population`, `timezone`)
                    VALUES
                    (:geoname_id, :city, :ascii_city, :lat, :long, :country_code, :state_code, :country, :state, :population, :timezone)
        ';

        $prepared = $pdo->prepare($sql);

        // loop through file to get contents
        while (!$file->eof()) {

            $row = $file->fgets();
            if (trim($row) == '') {
                break;
            }
            $row = explode("\t", $row);

            try {

                // there are certain places that simply don't have a state (e.g. Singapore, Vatican)
                if (!array_key_exists($row[8] . '.' . $row[10], $state_list)) {
                    $composite_state_code = '';
                } else {
                    $composite_state_code = $state_list[$row[8] . '.' . $row[10]];
                }

                $prepared->execute(
                    [
                        ':geoname_id'   => $row[0],
                        ':city'         => $row[1],
                        ':ascii_city'   => $row[2],
                        ':lat'          => $row[4],
                        ':long'         => $row[5],
                        ':country_code' => $row[8],
                        ':state_code'   => $row[10],
                        ':country'      => $country_list[$row[8]],
                        ':state'        => $composite_state_code,
                        ':population'   => $row[14],
                        ':timezone'     => $row[17]
                    ]
                );
            } catch (Exception $e) {
                dd($e);
            }

            $counter++;
            if ($counter % 1000 == 0) {
                $this->comment(
                    sprintf(
                        "%-30s",
                        "Processing #$counter"
                    ) .
                    "{$row[1]}, " . ($composite_state_code ? : '') . ", " . trim($country_list[$row[8]])
                );
            }

        }

        $this->info("Main import successful, $counter rows over " . (microtime(true) - $start) . " seconds.");

        $this->info("Trimming and updating administration district table with city population data...");
        $this->comment("States with no cities will be trimmed.");

        $trimmed = 0;
        $populated = 0;
        $total_population = 0;

        for ($i = 1; $i <= $total_states; $i++) {
            $state = DB::table('lookup_geoname_states')->find($i);
            $pop = DB::select(
                'SELECT sum(population) AS pop FROM `lookup_geoname_places` WHERE `state_code` = ? AND `country_code` = ?',
                [$state->state_code, $state->country_code]
            )[0]->pop;

            if (!$pop) {
                // if the population is 0 or not found, delete the state.
                DB::table('lookup_geoname_states')->delete($i);
                $trimmed++;
            } else {
                // otherwise, update the population and country columns.
                $country = DB::table('lookup_countries')->where('code', $state->country_code)->pluck('name');
                DB::table('lookup_geoname_states')->where('id', $i)->update(
                    ['population' => $pop, 'country' => $country]
                );
                $populated++;
                $total_population += (int)$pop;
            }


            if ($i % 250 == 0) {
                $this->comment(
                    sprintf(
                        "%-40s %60s",
                        "Processing #$i of {$total_states}",
                        number_format($total_population) . " people counted"
                    )
                );
            }
        }

        $this->info('Finished updating administration district table.');
        $this->comment("Trimmed $trimmed states for not being represented ($populated states remain).");
        $this->comment("This database accounts for " . number_format($total_population) . " people in the world.");

        $this->info('Operation complete.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array(
                'source',
                InputArgument::OPTIONAL,
                'Source geonames file',
                'app/data/geonames-cities-15000.txt'
            ),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
