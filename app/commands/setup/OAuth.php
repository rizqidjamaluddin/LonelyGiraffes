<?php

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SeedOAuth extends Command
{
    const LOG_STREAM = 'LG-Setup';
    const LG_CLIENT_ID = 'com.lonelygiraffes';
    const LG_CLIENT_SECRET = 'SEJ1IGGwz2z9oHoCVtxmlO9tY5Xc7MVA';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lgsetup:oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Add base data (scopes, clients, etc) into OAuth tables";

    /**
     * @var Giraffe\Logging\Log
     */
    protected $log;

    /**
     * Create a new command instance.
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
        /** @var Giraffe\Logging\Log $log */
        $this->log = App::make('Giraffe\Logging\Log');
        $this->output->getFormatter()->setStyle('sql', new OutputFormatterStyle('blue'));


        // start
        $this->info('Inserting base Lonely Giraffes data into OAuth tables.');
        $this->log->info("initializing OAuth insertion");

        // check tables migrated
        if (!Schema::hasTable('oauth_clients') || !Schema::hasTable('oauth_scopes')) {
            $this->error('OAuth tables not found in database; check that migrations have been run');
            $this->log->notice('aborting due to missing tables');
            return;
        }

        /**
         * Insert com.lonelygiraffes into clients table.
         */
        if (!DB::table('oauth_clients')->where('id', self::LG_CLIENT_ID)->exists()) {
            $this->info('"com.lonelygiraffes" not found in clients table.');
            DB::table('oauth_clients')->insert(
              [
                  'id'         => self::LG_CLIENT_ID,
                  'secret'     => self::LG_CLIENT_SECRET,
                  'name'       => 'Lonely Giraffes Production',
                  'created_at' => new Carbon,
                  'updated_at' => new Carbon
              ]
            );
            $this->line(
                 "<sql>Inserting new client: com.lonelygiraffes with secret [" . self::LG_CLIENT_SECRET . "].</sql>"
            );
        } else {
            $this->comment('Client entry exists, continuing...');
        };

        /**
         * Insert "basic" generic scope.
         */
        if (!DB::table('oauth_scopes')->where('scope', 'basic')->exists()) {
            $this->info('"basic" scope not found in scopes table.');
            DB::table('oauth_scopes')->insert(
              [
                  'scope'      => 'basic',
                  'name'       => 'Basic',
                  'created_at' => new Carbon,
                  'updated_at' => new Carbon
              ]
            );
            $this->line(
                 "<sql>Inserting entry for basic scope.</sql>"
            );
        } else {
            $this->comment('Basic scope entry exists, continuing...');
        };

        $this->info('Operation complete.');

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
        return [
        ];
    }

}
