<?php

use Illuminate\Console\Command;

class EmulateFail extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lgdev:emulate-fail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Return an exit code of 0 for testing purposes.';

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
        $this->line('Returning exit code of 1.');
        return 1;
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