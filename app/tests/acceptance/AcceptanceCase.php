<?php

use Giraffe\Authorization\Gatekeeper;

abstract class AcceptanceCase extends TestCase
{

    /**
     * @var Gatekeeper
     */
    protected $gatekeeper;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
    }


    protected function createAdministratorAccount()
    {
        $userRepository = App::make('Giraffe\Users\UserRepository');
        $admin = $userRepository->create(
            [
                'firstname' => 'Admin',
                'lastname' => 'Member',
                'gender' => 'M',
                'email' => 'admin@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'admin',
                'hash' => Str::random(32),
            ]
        );
        return $admin;
    }

    protected function createMemberAccount()
    {
        $userRepository = App::make('Giraffe\Users\UserRepository');
        $member = $userRepository->create(
            [
                'firstname' => 'Lonely',
                'lastname' => 'Giraffe',
                'gender' => 'M',
                'email' => 'hello@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'member',
                'hash' => Str::random(32),
            ]
        );
        return $member;
    }

} 