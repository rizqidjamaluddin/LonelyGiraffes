<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Carbon\Carbon;
use Faker\Factory as Faker;
use Giraffe\Models\UserModel;

class UsersTableSeeder extends Seeder {

	public function run()
	{
        DB::table('users')->truncate();

		$faker = Faker::create();

		foreach(range(1, 10) as $index)
		{
            if (rand(0,1) == 1) {
                $first = $faker->firstName('male');
                $last = $faker->lastName;
                $nick = $first . $last;
                $gender = 'M';
            } else {
                $first = $faker->firstName('female');
                $last = $faker->lastName;
                $nick = $first . $last;
                $gender = 'F';
            }

			UserModel::create([
                'public_id' => strtolower($first),
                'nickname' => $nick,
                'firstname' => $first,
                'lastname' => $last,
                'email' => lcfirst($first) . '@' . $faker->domainName,
                'password' => Hash::make(lcfirst($first)),
                'date_of_birth' => Carbon::create($faker->randomNumber(1970, 1997), $faker->month, $faker->dayOfMonth, 0, 0, 0) ,
                'gender' => $gender
			]);
		}
	}

}