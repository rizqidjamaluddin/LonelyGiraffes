<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RunTests extends Command
{

    /**
     * @var array List of commands to run
     */
    protected $commands = [
        'unit'       => 'phpunit --testsuite=unit',
        'acceptance.general' => 'phpunit --testsuite=acceptance-general',
        'acceptance.chat' => 'phpunit --testsuite=acceptance-chat',
        'acceptance.geolocation' => 'phpunit --testsuite=acceptance-geolocation',
        'component'  => 'phpunit --testsuite=component',
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lgdev:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run full gauntlet of LG tests.';

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
        $this->info('Starting test gauntlet.');
        /** @var \Symfony\Component\Console\Helper\FormatterHelper $formatter */
        $formatter = $this->getHelperSet()->get('formatter');
        $this->output->getFormatter()->setStyle('rep', new OutputFormatterStyle('blue', null, []));
        $this->output->getFormatter()->setStyle('name', new OutputFormatterStyle('red', null, []));
        $this->output->getFormatter()->setStyle('exec', new OutputFormatterStyle('magenta', null, []));
        $this->output->getFormatter()->setStyle('out', new OutputFormatterStyle('black', null, []));
        $this->output->getFormatter()->setStyle('safe', new OutputFormatterStyle('white', 'green', []));
        $this->output->getFormatter()->setStyle('rep-ok', new OutputFormatterStyle('green', null, []));
        $this->output->getFormatter()->setStyle('rep-fail', new OutputFormatterStyle('red', null, []));
        $this->drawLine();

        $failing = false;
        $failed = [];
        $succeeded = [];

        foreach ($this->commands as $process => $command) {
            $this->line("<exec>$process > $command</exec>");
            $output = [];
            $startTime = microtime(true);
            $lastLine = exec($command, $output, $exitCode);
            $duration = round(microtime(true) - $startTime, 2);
            if ($exitCode == 0) {
                $this->line("<rep>Test \"<name>$process</name>\" completed successfully ({$duration}s).</rep>");
                $this->info("\n");
                $this->line($lastLine);
                $succeeded[] = $process;
                $this->drawLine();
            } else {
                // test fail
                $this->error('Test failed!');
                foreach ($output as $line) {
                    $this->line("[FAIL] <out>$line</out>");
                }
                $failing = true;
                $failed[] = $process;
                $this->drawLine();

            }
        }



        foreach ($failed as $test) {
            $this->line("[FAIL] <rep-fail>$test</rep-fail>");
        }
        foreach ($succeeded as $test) {
            $this->line("[OK] <rep-ok>$test</rep-ok>");
        }

        if ($failing) {
            $this->error("Tests FAIL");
            return 1;
        } else {
            $this->line("<safe>Tests OK</safe>");
            return 0;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array( //			array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array( //			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

    protected function drawLine()
    {
        $this->info(str_repeat('-', 60));
    }

}
