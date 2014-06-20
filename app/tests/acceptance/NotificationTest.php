<?php

use Giraffe\Notifications\NotificationContainerRepository;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotificationModel;

class NotificationTest extends AcceptanceCase
{

    /**
     * @var NotificationContainerRepository
     */
    protected $containerRepository;

    /**
     * @var NotificationService
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = App::make('Giraffe\Notifications\NotificationService');
        $this->containerRepository = App::make('Giraffe\Notifications\NotificationContainerRepository');
    }

    /**
     * @test
     */
    public function a_user_can_get_their_notifications_when_empty()
    {

        $user = $this->createMemberAccount();
        $this->be($user);

        $request = $this->call('GET', 'api/notifications');

        $response = json_decode($request->getContent());
        $this->assertResponseOk();
        $this->assertEquals(count($response), 0);
    }

    /**
     * @test
     */
    public function a_guest_has_no_notifications()
    {
        $this->call('GET', 'api/notifications');
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function a_user_can_see_a_notification()
    {
        $user = $this->createMemberAccount();
        $this->be($user);
        $this->service->queue(SystemNotificationModel::make('Test notification'), $user);

        $request = $this->call('GET', 'api/notifications');
        $this->assertResponseOk();
        $response = json_decode($request->getContent());
        $notifications = $response->data;
        $this->assertEquals(count($notifications), 1);
        $this->assertEquals($notifications[0]->type, 'SystemNotificationModel');
        $this->assertEquals($notifications[0]->body->message, 'Test notification');
    }

    /**
     * @test
     */
    public function a_user_can_see_a_collection_of_notifications()
    {
        $user = $this->createMemberAccount();
        $this->be($user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $user);

        $request = $this->call('GET', 'api/notifications');
        $this->assertResponseOk();
        $response = json_decode($request->getContent());
        $notifications = $response->data;
        $this->assertEquals(count($notifications), 3);
        $this->assertEquals($notifications[0]->body->message, 'Test Notification 1');
        $this->assertEquals($notifications[1]->body->message, 'Test Notification 2');
        $this->assertEquals($notifications[2]->body->message, 'Test Notification 3');
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_a_notification()
    {
        $user = $this->createMemberAccount();
        $this->be($user);
        $generated = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);
        $generated2 = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);

        $r = $this->call('DELETE', 'api/notifications/' . $generated->hash);
        $this->assertResponseOk();
        $notifications = $this->service->getUserNotifications($user);
        $this->assertEquals(count($notifications), 1);


        $r = $this->call('DELETE', '/api/notifications/' . $generated2->hash);
        $this->assertResponseOk();
        $notifications = $this->service->getUserNotifications($user);
        $this->assertEquals(count($notifications), 0);
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_one_out_of_many_notifications()
    {
        $user = $this->createMemberAccount();
        $this->be($user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);
        $generated = $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $user);

        $r = $this->call('DELETE', 'api/notifications/' . $generated->hash);
        $this->assertResponseOk();
        // double check to ensure notification is dismissed, but not others
        $notifications = $this->service->getUserNotifications($user);
        $this->assertEquals(count($notifications), 2);
        // these are NotificationContainerModel objects, so the property is ->notification to get the body
        $this->assertEquals($notifications[0]->notification->message, 'Test Notification 1');
        $this->assertEquals($notifications[1]->notification->message, 'Test Notification 3');

    }

    /**
     * @test
     */
    public function a_client_can_dismiss_all_notifications()
    {
        $user = $this->createMemberAccount();
        $this->be($user);
        $m1 = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);
        $m2 = $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $user);
        $m3 = $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $user);
        $internalCheck = $m1->notification;

        $this->call('POST', 'api/notifications/clear');
        $this->assertResponseOk();
        // double-check notifications to ensure no undismissed ones are around
        $notifications = $this->service->getUserNotifications($user);
        $this->assertEquals(count($notifications), 0);

        // internal check to make sure the actual sub-children are gone too
        $check = SystemNotificationModel::find($internalCheck->id);
        $this->assertEquals($check, null);

    }


    /**
     * @test
     */
    public function a_user_cannot_see_the_notifications_for_another_user()
    {
        $user = $this->createMemberAccount();
        $otherUser = $this->createOtherAccount();
        $this->be($otherUser);

        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $user);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $user);

        $this->service->queue(SystemNotificationModel::make('My Notification'), $otherUser);

        $request = $this->call('GET', 'api/notifications');

        $this->assertResponseOk();
        $notifications = json_decode($request->getContent())->data;
        $this->assertEquals(1, count($notifications));
        $this->assertEquals('My Notification', $notifications[0]->body->message);

    }

    /**
     * @test
     */
    public function a_user_cannot_dismiss_other_user_notifications()
    {
        $user = $this->createMemberAccount();
        $otherUser = $this->createOtherAccount();
        $this->be($otherUser);
        $container = $this->service->queue(SystemNotificationModel::make('Test Notification'), $user);

        $request = $this->call('DELETE', 'api/notifications/' . $container->hash);

        $this->assertResponseStatus(403);
        $check = $this->service->getUserNotifications($user);
        $this->assertEquals(1, count($check));
        $this->assertEquals('Test Notification', $check[0]->notification->message);
    }
} 