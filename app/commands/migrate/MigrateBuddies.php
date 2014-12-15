<?php
use Illuminate\Console\Command;

class MigrateBuddies extends Command
{

    protected $name = 'lg:migrate:buddies';
    protected $description = 'Migrate v1 buddies to new database';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->confirm("Migrating buddies from version 1. Continue?");
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
