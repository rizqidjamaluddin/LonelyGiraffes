<?php

use Giraffe\Notifications\NotificationRepository;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotification\SystemNotificationModel;
use Giraffe\Notifications\SystemNotification\SystemNotificationRepository;
use Giraffe\Users\UserService;

class NotificationServiceTest extends TestCase
{
    /**
     * @var NotificationService
     */
    protected $service;

    /**
     * @var NotificationRepository
     */
    protected $repository;

    /**
     * @var SystemNotificationRepository
     */
    protected $systemNotificationRepository;

    public function setUp()
    {
        parent::setUp();
        $this->refreshApplication();
        Artisan::call('migrate');

        $this->service = App::make(NotificationService::class);
        $this->repository = App::make(NotificationRepository::class);

        $this->systemNotificationRepository = App::make(SystemNotificationRepository::class);
    }

    /**
     * @test
     */
    public function it_can_queue_a_notification()
    {
        $testUser = $this->generateTestUser();
        $notification = new SystemNotificationModel(['title' => 'Test Notification', 'message' => 'foo']);
        $this->systemNotificationRepository->save($notification);
        $container = $this->service->issue($notification, $testUser);

        $this->assertTrue(!is_null($container), 'NotificationService::issue should return an instance of NotificationModel');
        $this->assertTrue($container instanceof \Giraffe\Notifications\NotificationModel);

        $check = $this->repository->get($container->id);
        $this->assertEquals($check->destination->hash, $testUser->hash);
        $this->assertEquals($check->notifiable()->title, 'Test Notification');
        $this->assertEquals($check->notifiable()->message, 'foo');
    }

    /**
     * This is how short polling will work; client ping server with the hash of the last received notification, server
     * should return all notifications since this one.
     */
    public function it_can_get_all_waiting_notifications_based_on_the_last_received_hash()
    {

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
                'name' => 'Lonely',
                'gender'    => 'M'
            ]
        );
        return $testUser;
    }
} 