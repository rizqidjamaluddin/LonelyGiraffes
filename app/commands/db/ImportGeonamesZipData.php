<?php

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ImportGeonamesZipData extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lg:db:geonames-postal';

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

        $path = app_path() . $this->argument('source');
        $filesize = File::size($path);
        $this->info('Importing Geonames postal code data.');
        $this->info("Source File: $path ($filesize bytes)");

        $this->info('> Creating lookup table...');
        DB::statement('drop table if exists `lookup_geoname_postal_codes`');
        Schema::create(
              'lookup_geoname_postal_codes',
                  function (Blueprint $table) {
                      $table->increments('id');
                      $table->string('code');
                      $table->string('city');
                      $table->string('state_code');
                      $table->string('country_code');
                      $table->index('code');
                  }
        );
        $source = new SplFileObject($path);
        $insert = $pdo->prepare(
            'INSERT INTO `lookup_geoname_postal_codes` (`code`, `city`, `state_code`, `country_code`) VALUES (:code, :city, :state, :country)'
        );

        $this->info('> Starting data import...');

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $source->seek($source->getSize());
        $linesTotal = $source->key();
        $source->rewind();
        $progress->start($this->output, $linesTotal + 1);

        $locationCount = 0;
        $insertCount = 0;
        $start = microtime(true);
        while (!$source->eof()) {
            $row = $source->fgets();
            $row = explode("\t", $row);

            $code = isset($row[1]) ? $row[1] : null;
            $city = isset($row[2]) ? $row[2] : null;
            $state = isset($row[4]) ? $row[4] : null;
            $country = isset($row[0]) ? $row[0] : null;

            $locationCount++;

            // refuse malformed rows
            if (!$code || !$city || !$state || !$country) continue;

            $insert->execute(
                [
                    'code' => $code,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country
                ]
            );

            $insertCount++;
            if ($locationCount % 100 == 0 ) $progress->setCurrent($locationCount);

        }
        $progress->finish();
        $time = microtime(true) - $start;
        $this->info("Finished importing postal codes ($insertCount total, took $time seconds).");
        $this->comment("Discarded " . ($locationCount - $insertCount) . " locations due to malformed data.");

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
                '/data/geonames-postal/geonames-zipcodes.txt'
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
