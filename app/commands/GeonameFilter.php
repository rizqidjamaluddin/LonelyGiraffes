<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GeonameFilter extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lgutil:geonamefilter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new geonames file, filtered by population from the main file.';

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

        $path = __DIR__ . '/../../' . $this->option('source');
        $file = new SplFileObject($path);
        $destination = new SplFileObject(__DIR__ . '/../../' . $this->option('destination'), 'w+');
        $destination->ftruncate(0);

        $this->info('Filtering Geonames database to cities with a population of over ' . $this->option('minpop') . '.');
        $this->info("Source File: " . $file->getRealPath() . " (" . $file->getSize() . " bytes)");


        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $file->seek($file->getSize());
        $linesTotal = $file->key();
        $file->rewind();
        $progress->start($this->output, $linesTotal + 1);
        $count = 0;
        $added = 0;

        while (!$file->eof()) {

            $rawRow = $file->fgets();
            if (trim($rawRow) == '') {
                break;
            }
            $row = explode("\t", $rawRow);

            $pop = $row[14];

            if ($pop > $this->option('minpop')) {
                $destination->fwrite($rawRow);
                $added++;
            }
            $count++;
            if ($count % 100 == 0) {
                $progress->setCurrent($count);
            }
        }

        $progress->setCurrent($linesTotal+1);
        $progress->finish();
        $this->info('Wrote ' . $added . ' cities (' . $destination->getSize() . ' bytes) to ' . $destination->getRealPath());
        $this->info('Operation complete.');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array (
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(

            array(
                'source',
                null,
                InputOption::VALUE_OPTIONAL,
                'Source geonames file.',
                'app/data/geonames-cities-15000.txt'
            ),
            array(
                'destination',
                null,
                InputOption::VALUE_OPTIONAL,
                'Destination file to write to.',
                'app/data/geonames-cities-filtered.txt'
            ),
            array(
                'minpop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Minimum population required for a city for it to be included',
                '1000000'
            ),
        );
    }

}
