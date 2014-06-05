<?php

use Giraffe\Authorization\GiraffeGatekeeperProvider;
use Giraffe\Users\UserModel;

class GiraffeGatekeeperTest extends TestCase
{

    const TEST = 'Giraffe\Authorization\GiraffeGatekeeperProvider';
    const REPOSITORY = 'Giraffe\Users\UserRepository';


    /** @var GiraffeGatekeeperProvider $provider */
    protected $provider;

    /** @var UserModel */
    protected $mainMember;
    protected $otherMember;
    protected $modMember;
    protected $adminMember;

    public function setUp()
    {

        parent::setUp();
        $this->refreshApplication();
        Artisan::call('migrate');

        /** @var Giraffe\Users\UserRepository $userRepository */
        $userRepository = App::make('Giraffe\Users\UserRepository');

        $this->mainMember = $userRepository->create(
            [
                'firstname' => 'Main',
                'lastname' => 'Member',
                'gender' => 'M',
                'email' => 'main@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'member',
                'hash' => Str::random(20),
            ]
        );
        $this->otherMember = $userRepository->create(
            [
                'firstname' => 'Other',
                'lastname' => 'Member',
                'gender' => 'M',
                'email' => 'other@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'member',
                'hash' => Str::random(20),
            ]
        );
        $this->modMember = $userRepository->create(
            [
                'firstname' => 'Mod',
                'lastname' => 'Member',
                'gender' => 'M',
                'email' => 'mod@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'mod',
                'hash' => Str::random(20),
            ]
        );
        $this->adminMember = $userRepository->create(
            [
                'firstname' => 'Admin',
                'lastname' => 'Member',
                'gender' => 'M',
                'email' => 'admin@lonelygiraffes.net',
                'password' => 'password',
                'role' => 'admin',
                'hash' => Str::random(20),
            ]
        );

        $this->provider = App::make(self::TEST);


    }

    /**
     * @test
     */
    public function it_can_check_a_user_permission_from_their_group()
    {
        $this->assertFalse($this->provider->checkIfGuestMay('test', 'test'));
        $this->assertTrue($this->provider->checkIfUserMay($this->mainMember, 'test', 'test'));
        $this->assertTrue($this->provider->checkIfUserMay($this->modMember, 'test', 'test'));
        $this->assertTrue($this->provider->checkIfUserMay($this->adminMember, 'test', 'test'));
    }

    /**
     * @test
     */
    public function all_posts_are_public_by_default()
    {
        $this->assertTrue($this->provider->checkIfGuestMay('read', 'post'));
    }

    /**
     * @test
     */
    public function users_can_be_deactivated_only_by_themselves_or_an_admin()
    {
        /** @var Giraffe\Users\UserRepository $userRepository */
        $userRepository = App::make('Giraffe\Users\UserRepository');
        /** @var UserModel $user */
        $user = $userRepository->create(
            [
                'firstname' => 'Lonely',
                'lastname' => 'Giraffe',
                'gender' => 'M',
                'email' => 'hello@lonelygiraffes.net',
                'password' => 'password',
                'hash' => Str::random(20),
            ]
        );

        // reload user from repository
        $user = $userRepository->get($user->id);

        $this->assertFalse($this->provider->checkIfGuestMay('deactivate', 'user', $user));
        $this->assertTrue($this->provider->checkIfUserMay($user, 'deactivate', 'user', $user));
        $this->assertFalse($this->provider->checkIfUserMay($this->otherMember, 'deactivate', 'user', $user));
        $this->assertFalse($this->provider->checkIfUserMay($this->modMember, 'deactivate', 'user', $user));
        $this->assertTrue($this->provider->checkIfUserMay($this->adminMember, 'deactivate', 'user', $user));
    }

} 