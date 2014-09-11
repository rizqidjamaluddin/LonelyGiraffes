<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Carbon\Carbon;
use Faker\Factory as Faker;
use Giraffe\Buddies\BuddyRequests\BuddyRequestModel;
use Giraffe\Users\UserModel;
use Giraffe\Buddies\BuddyModel;

class UsersTableSeeder extends Seeder {

	public function run()
	{
        DB::table('users')->truncate();
        DB::table('buddies')->truncate();
        DB::table('buddy_requests')->truncate();

		$faker = Faker::create();

        $fake_users = [];

		foreach(range(1, 10) as $index)
		{
            if (rand(0,1) == 1) {
                $name = $faker->name('male');
                $gender = 'M';
            } else {
                $name = $faker->name('female');

                $gender = 'F';
            }

			$user = UserModel::create([
                'hash' => Str::random(32),
                'name' => $name,
                'email' => str_replace(' ', '_', lcfirst($name)) . '@' . $faker->domainName,
                'password' => Hash::make(lcfirst($name)),
                'date_of_birth' => Carbon::create($faker->randomNumber(1970, 1997), $faker->month, $faker->dayOfMonth, 0, 0, 0) ,
                'gender' => $gender
			]);

            //Create some buddies & buddy requests
            if(count($fake_users) >= 4) {
                $rand_users = array_rand($fake_users, 4);

                $rand_users_request = [array_pop($rand_users), array_pop($rand_users)];
                foreach ($rand_users_request as $rand) {
                    BuddyRequestModel::create([
                        'from_user_id' => $user->id,
                        'to_user_id' => $fake_users[$rand]->id
                    ]);
                }

                foreach ($rand_users as $rand) {
                    BuddyModel::create([
                        'user1_id' => $fake_users[$rand]->id,
                        'user2_id' => $user->id
                    ]);
                }
            }

            array_push($fake_users, $user);
		}
	}

}