<?php

use Giraffe\Notifications\NotificationContainerRepository;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\Types\SystemNotificationModel;
use Giraffe\Users\UserService;

class NotificationServiceTest extends TestCase
{
    /**
     * @var NotificationService
     */
    protected $service;

    /**
     * @var NotificationContainerRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->refreshApplication();
        Artisan::call('migrate');

        $this->service = App::make('Giraffe\Notifications\NotificationService');
        $this->repository = App::make('Giraffe\Notifications\NotificationContainerRepository');
    }

    /**
     * @test
     */
    public function it_can_queue_a_notification()
    {
        $testUser = $this->generateTestUser();
        $notification = new SystemNotificationModel(['title' => 'Test Notification', 'message' => 'foo']);
        $container = $this->service->queue($notification, $testUser);

        $this->assertTrue(!is_null($container), 'Service::queue should return an instance of NotificationContainerModel');
        $this->assertEquals(get_class($container), 'Giraffe\Notifications\NotificationContainerModel');

        $check = $this->repository->get($container->id);
        $this->assertEquals($check->destination()->hash, $testUser->hash);
        $this->assertEquals($check->notification->title, 'Test Notification');
    }

    /**
     * @return \Giraffe\Users\UserModel
     */
    protected function generateTestUser()
    {
        /** @var UserService $userService */
        $userService = App::make('Giraffe\Users\UserService');
        $testUser = $userService->createUser(
            [
                "email"     => 'hello@lonelygiraffes.com',
                "password"  => 'password',
                'firstname' => 'Lonely',
                'lastname'  => 'Giraffe',
                'gender'    => 'M'
            ]
        );
        return $testUser;
    }
} 