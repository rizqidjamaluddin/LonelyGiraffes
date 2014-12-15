<?php
use Illuminate\Console\Command;

class MigrateChats extends Command
{

    protected $name = 'lg:migrate:chats';
    protected $description = 'Migrate v1 chats to new database';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        $this->confirm("Migrating conversations from version 1. Continue?");
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
