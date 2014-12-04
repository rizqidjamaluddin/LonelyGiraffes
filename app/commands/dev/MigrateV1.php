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
                'password' => $this->handleLegacyCodeIgniterPassword($user['password']),
                'hash' => Str::random(32),
                'role' => 'member',
                'created_at' => $user['joined'],
                'updated_at' => time(),
            ];

            // enter location data
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

            // handle avatars

            try {
                $userRepository->create($user);
                $this->info('Created user for ' . $user['email'] . '.');
            } catch (Exception $e) {
                $this->error('Unable to create user for ' . $user['email'] . ': ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * I made 20 people on #laravel-offtopic go WTF at the CI encrypt library. SCORE.
     *
     * @param $password
     */
    protected function handleLegacyCodeIgniterPassword($password)
    {
        if ($password[0] == '$') {
            return $password;
        }
        $key = '2a7af90c898ce26ea993398d966615bd'; // md5 of 'DXTDO4O3pxLTDo53LesTbtYsFXFFW2oV', it was CI's idea;

        $data = $this->_remove_cipher_noise($password, $key);
        $init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $init_vect = substr($data, 0, $init_size);
        $data = substr($data, $init_size);
        return Hash::make(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $init_vect), "\0"));
    }

    function _remove_cipher_noise($data, $key) {
        $keyhash = sha1($key);
        $keylen = strlen($keyhash);
        $str = '';

        for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
            if ($j >= $keylen) {
                $j = 0;
            }

            $temp = ord($data[$i]) - ord($keyhash[$j]);

            if ($temp < 0) {
                $temp = $temp + 256;
            }

            $str .= chr($temp);
        }

        return $str;
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