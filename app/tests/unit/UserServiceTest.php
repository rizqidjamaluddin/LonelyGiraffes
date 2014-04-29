<?php

use Giraffe\Services\UserService;

/**
 * @coversDefaultClass \Giraffe\Services\UserService
 */
class UserServiceTest extends TestCase
{

    /**
     * @var Giraffe\Services\UserService
     */
    private $userService;

    public function setUp()
    {
        // fake returned user data
        $data_for_user_id_1 = json_decode("{
            'settings' : {
                'use_nickname': true
            }
        }");

        // mocking
        $userRepository = Mockery::mock('Giraffe\Repositories\UserRepository');
        $userRepository->shouldReceive('getByIdWithSettings')->with(1)->andReturn($data_for_user_id_1);
        $userRepository->shouldReceive('setUserNicknameSettingById')->with(2, true)->andReturn(true);
        $userRepository->shouldReceive('deleteByIdWithEmailConfirmation')->with(3, 'not-user3@example.com')->andReturn(false);
        $userRepository->shouldReceive('deleteByIdWithEmailConfirmation')->with(3, 'user3@example.com')->andReturn(true);
        $userRepository->shouldReceive('reactivateById')->with(3)->andReturn(true);

        // init
        App::instance('Giraffe\Repositories\UserRepository', $userRepository);
        $this->userService = App::make('Giraffe\Services\UserService');
    }

    /**
     * @test
     */
    public function it_can_get_user_nickname_settings_from_a_user_id()
    {
        $setting = $this->userService->getUserNicknameSetting(1);
        $this->assertTrue($setting);
    }

    /**
     * @test
     */
    public function it_can_set_a_user_nickname_setting_from_id()
    {
        $op = $this->userService->setUserNicknameSetting(2, true);
        $this->assertTrue($op);
    }

    /**
     * @test
     */
    public function it_fails_to_deactivate_a_user_account_if_the_email_does_not_match()
    {
        $op = $this->userService->deactivateUser(3, 'not-user3@example.com');
        $this->assertFalse($op);
    }

    /**
     * @test
     */
    public function it_can_deactivate_a_user_account()
    {
        $op = $this->userService->deactivateUser(3, 'user3@example.com');
        $this->assertTrue($op);
    }

    /**
     * @test
     */
    public function it_can_reactivate_a_user_account()
    {
        $op = $this->userService->reactivateUser(3);
        $this->assertTrue($op);
    }

    public function tearDown()
    {
        Mockery::close();
    }
} 