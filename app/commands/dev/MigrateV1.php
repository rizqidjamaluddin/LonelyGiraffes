<?php

use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationService;
use Giraffe\Images\ImageRepository;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Console\Command;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;
use League\Flysystem\Filesystem;

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

        /** @var ImageRepository $imageRepository */
        $imageRepository = \App::make(ImageRepository::class);

        /** @var Callable $imageFS */
        $imageFS = Config::get('images.medium');
        /** @var Filesystem $image */
        $image = $imageFS();

        /** @var Callable $stagingFS */
        $stagingFS = Config::get('images.staging');
        /** @var Filesystem $staging */
        $staging = $stagingFS();

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

            try {
                /** @var UserModel $savedUser */
                $savedUser = $userRepository->create($user);
                $this->info('Created user for ' . $user['email'] . '.');
            } catch (Exception $e) {
                $this->error('Unable to create user for ' . $user['email'] . ': ' . $e->getMessage());
                continue;
            }

            // handle avatars
            if ($user['profile_picture'] != '/images/default_picture.jpg') {
                $img = ImageManagerStatic::make(storage_path() . '/old-avatars' . $user['profile_picture']);

                $data = [];
                $data['user_id'] = $savedUser->id;
                $data['hash'] = Str::random(18);
                $data['extension'] = $img->extension;
                $data['image_type_id'] = 1;

                $imageRepository->create($data);

                if ($img->width() > 400 || $img->height() > 400) {
                    $img->fit(400,400);
                }

                $img->save(storage_path() . '/image-staging/' . $data['hash'] . '.' . $data['extension']);
                $img->fit(100,100)->save(storage_path() . '/image-staging/' . $data['hash'] . '_thumb.' . $data['extension']);

                $file = $staging->read(storage_path() . '/image-staging/' . $data['hash'] . '.' . $data['extension']);
                $image->put($data['hash'] . '.' . $data['extension'], $file);

                $file = $staging->read(storage_path() . '/image-staging/' . $data['hash'] . '_thumb.' . $data['extension']);
                $image->put($data['hash'] . '_thumb.' . $data['extension'], $file);

            }
        }

        return true;
    }

    /**
     * I made 20 people on #laravel-offtopic go WTF at the CI encrypt library. SCORE.
     *
     * @param $password
     * @return string
     */
    protected function handleLegacyCodeIgniterPassword($password)
    {
        if ($password[0] == '$') {
            return $password;
        }
        $key = '2a7af90c898ce26ea993398d966615bd'; // md5 of 'DXTDO4O3pxLTDo53LesTbtYsFXFFW2oV', it was CI's idea;

        $data = $this->removeCipherNoise($password, $key);
        $init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $init_vect = substr($data, 0, $init_size);
        $data = substr($data, $init_size);
        return Hash::make(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $init_vect), "\0"));
    }

    public function removeCipherNoise($data, $key) {
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