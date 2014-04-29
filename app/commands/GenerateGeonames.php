<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateGeonames extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lgdb:geonames';

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

        $path = __DIR__ . '/../data/geonames-cities-15000.txt';
        $filesize = File::size($path);
        $this->info('Importing Geonames database.');
        $this->info("Source File: $path ($filesize bytes)");

        $this->info('> Creating countries list table...');
        $countries = new SplFileObject(__DIR__ . '/../data/general-countries.csv');
        DB::statement('drop table if exists `lookup_countries`');
        DB::statement('CREATE TABLE `lookup_countries` (
           `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
           `code` char(2) DEFAULT NULL,
           `name` varchar(200) DEFAULT NULL,
           PRIMARY KEY (`id`),
           KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->info('> Importing countries list table...');
        $country_statement = $pdo->prepare('INSERT INTO `lookup_countries` (`code`, `name`) VALUES (:code, :name)');
        while (!$countries->eof()) {
            list($code, $country_name) = $countries->fgetcsv();
            // hotfix for countries with unicode characters
            if ($code == 'AX') $country_name = 'Åland Islands';
            if ($code == 'CI') $country_name = 'Côte d\'Ivoire';
            $country_list[$code] = $country_name;
            $country_statement->execute([
                    ':code' => $code,
                    ':name' => $country_name
                ]);
        }




        $this->info('> Creating administration district list table...');
        $countries = new SplFileObject(__DIR__ . '/../data/geonames-states.txt');
        DB::statement('drop table if exists `lookup_geoname_states`');
        DB::statement('CREATE TABLE `lookup_geoname_states` (
           `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
           `country_code` char(2) DEFAULT NULL,
           `state_code` varchar(20) DEFAULT NULL,
           `name` varchar(200) DEFAULT NULL,
           PRIMARY KEY (`id`),
           KEY `code` (`country_code`, `state_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->info('> Importing administration district list table...');
        $country_statement = $pdo->prepare('INSERT INTO `lookup_geoname_states` (`country_code`, `state_code`, `name`) VALUES (:country_code, :state_code, :name)');
        while (!$countries->eof()) {
            $row = $countries->fgets();
            $row = explode("\t", $row);
            list($country_code, $state_code) = explode('.', $row[0]);
            $state_list[$row[0]] = $row[1];
            $country_statement->execute([
                    ':country_code' => $country_code,
                    ':state_code' => $state_code,
                    ':name' => $row[1]
                ]);
        }

        $this->info('> Creating import table...');
        DB::statement('drop table if exists `lookup_geoname_places`');
        DB::statement(
            'CREATE TABLE `lookup_geoname_places` (
           `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
           `geoname_id` int(11) DEFAULT NULL,
           `city` varchar(200) DEFAULT NULL,
           `ascii_city` varchar(200) DEFAULT NULL,
           `lat` decimal(18,12) DEFAULT NULL,
           `long` decimal(18,12) DEFAULT NULL,
           `country_code` char(2) DEFAULT NULL,
           `state_code` varchar(20) DEFAULT NULL,
           `country` varchar(200) DEFAULT NULL,
           `state` varchar(200) DEFAULT NULL,
           `timezone` varchar(40) DEFAULT NULL,
           `population` bigint(20) DEFAULT NULL,
           PRIMARY KEY (`id`),
           KEY `lat` (`lat`,`long`),
           KEY `autocomplete` (`population`,`country`,`state`,`city`),
           KEY `city` (`city`,`ascii_city`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ');

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

        while (!$file->eof()) {
            $counter++;
            if ($counter % 1000 == 0) {
                $this->comment("Processing #$counter (". (microtime(true) - $start) ."s) | Memory use: " . memory_get_usage());
            }

            $row = $file->fgets();
            if (trim($row) == '') {
                break;
            }
            $row = explode("\t", $row);

            // $this->info("#$counter " . implode(',', [$row[0],$row[1],$row[2], $row[4], $row[5], $row[8], $row[10], $row[14], $row[17]]));

            try {
                if (!array_key_exists($row[8] . '.' . $row[10], $state_list)) {
                    $composite_state_code = '';
                } else {
                    $composite_state_code = $state_list[$row[8] . '.' . $row[10]];
                }

                $prepared->execute([
                        ':geoname_id'    => $row[0],
                        ':city'          => $row[1],
                        ':ascii_city'    => $row[2],
                        ':lat'           => $row[4],
                        ':long'          => $row[5],
                        ':country_code'  => $row[8],
                        ':state_code'    => $row[10],
                        ':country'       => $country_list[$row[8]],
                        ':state'         => $composite_state_code,
                        ':population'    => $row[14],
                        ':timezone'      => $row[17]
                    ]);
            }catch (Exception $e) {
                dd($e);
            }

        }
        $this->info("Import successful, $counter rows over " . (microtime(true) - $start) . " seconds");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
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
