<?php
use Illuminate\Console\Command;

class MigrateEvents extends Command
{

    protected $name = 'lg:migrate:events';
    protected $description = 'Migrate v1 events to new database';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
            $this->confirm("Migrating events from version 1. Continue?");
    }

    protected function getArguments()
    {
        return array();
    }

    protected function getOptions()
    {
        return array();
    }
} 
