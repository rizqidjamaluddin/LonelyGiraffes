<?php

use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationService;
use Giraffe\Users\UserRepository;
use Illuminate\Console\Command;

class MigrateV1 extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'lg:migrate:v1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate v1 data to new database';

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
        if (DB::table('users')->exists()) {
            $this->confirm("Note: this process is best done a blank database. Continue?");
        } else {
            $this->confirm("Migrating users, events, buddies and conversations from version 1. Continue?");
        }
        $this->info('Bumping up memory limit to 386M...');
        ini_set('memory_limit', '368M');
        $this->migrateUsers();
    }

    protected function migrateUsers()
    {
        $this->info('Processing users...');

        if (!Schema::hasTable('v1_users')) {
            $this->error('No V1 user database found (please name it v1_users), aborting user import.');
            return false;
        }

        $users = DB::table('v1_users')->get();
        $count = DB::table('v1_users')->count();

        $this->info($count . ' users will be imported.');

        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        /** @var LocationService $locationService */
        $locationService = \App::make(LocationService::class);


        foreach ($users as $user) {

            $name = $user['username'] ?: $user['firstname'] . ' ' . $user['lastname'];
            $user = [
                'name' => $name,
                'email' => $user['email'],
                'password' => $user['password'],
                'hash' => Str::random(32),
                'role' => 'member',
                'created_at' => $user['joined'],
                'updated_at' => time(),
            ];


            if ($user['zip_code']) {
                $locations = $locationService->search($user['zip_code'], 1);
                if (count($locations) != 0) {
                    /** @var Location $location */
                    $location = $locations[0];
                    $user['country'] = $location->country;
                    $user['state'] = $location->state;
                    $user['city'] = $location->city;
                    $user['cell'] = $locationService->getDefaultNearbySearchStrategy()->getCacheMetadata($location);
                }
            }

            $userRepository->create($user);
            $this->info('Created user for ' . $user['email'] . '.');
        }
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