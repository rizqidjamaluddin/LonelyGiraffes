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
        'acceptance' => 'phpunit --testsuite=acceptance',
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
        $this->output->getFormatter()->setStyle('rep', new OutputFormatterStyle('blue'));
        $this->output->getFormatter()->setStyle('name', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('exec', new OutputFormatterStyle('magenta'));
        $this->drawLine();

        foreach ($this->commands as $process => $command) {
            $this->line("<exec>$process > $command</exec>");
            $lastLine = exec($command, $output, $exitCode);
            if ($exitCode == 0) {
                $this->line("<rep>Test \"<name>$process</name>\" completed successfully.</rep>");
                $this->info("\n");
                $this->line($lastLine);
                $this->drawLine();
            } else {
                // test fail
                $this->error('Test failed!');

            }
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
