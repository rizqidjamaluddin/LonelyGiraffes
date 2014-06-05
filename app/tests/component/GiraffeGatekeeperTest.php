<?php

use Giraffe\Authorization\GiraffeGatekeeperProvider;
use Giraffe\Users\UserModel;

class GiraffeGatekeeperTest extends TestCase
{

    const TEST = 'Giraffe\Authorization\GiraffeGatekeeperProvider';
    const REPOSITORY = 'Giraffe\Users\UserRepository';

    public function setUp()
    {
        $this->refreshApplication();
        $repository = Mockery::mock(self::REPOSITORY);
        $memberUser = new UserModel;
        $memberUser->id = 1;
        $memberUser->nickname = 'member-user';
        $memberUser->role = 'member';
        $repository->shouldReceive('get')->with('member-user')->andReturn($memberUser);
        $modUser = new UserModel;
        $modUser->id = 2;
        $modUser->nickname = 'mod-user';
        $modUser->role = 'mod';
        $repository->shouldReceive('get')->with('mod-user')->andReturn($modUser);
        $adminUser = new UserModel;
        $adminUser->id = 3;
        $adminUser->nickname = 'admin-user';
        $adminUser->role = 'admin';
        $repository->shouldReceive('get')->with('admin-user')->andReturn($adminUser);
        App::instance(self::REPOSITORY, $repository);
    }

    /**
     * @test
     */
    public function it_can_check_a_user_permission_from_their_group()
    {
        /** @var GiraffeGatekeeperProvider $provider */
        $provider = App::make(self::TEST);
        $this->assertFalse($provider->checkIfGuestMay('test', 'test'));
        $this->assertTrue($provider->checkIfUserMay('member-user', 'test', 'test'));
        $this->assertTrue($provider->checkIfUserMay('mod-user', 'test', 'test'));
        $this->assertTrue($provider->checkIfUserMay('admin-user', 'test', 'test'));
    }


} 