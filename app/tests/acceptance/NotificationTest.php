<?php

use Giraffe\Notifications\NotificationContainerRepository;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotificationModel;
use Giraffe\Users\UserRepository;

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
    protected $userRepository;

    public function setUp()
    {
        parent::setUp();
        $this->userRepository = App::make('Giraffe\Users\UserRepository');
        $this->service = App::make('Giraffe\Notifications\NotificationService');
        $this->containerRepository = App::make('Giraffe\Notifications\NotificationContainerRepository');
    }

    /**
     * @test
     */
    public function a_user_can_get_their_notifications_when_empty()
    {

        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));

        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications), 0);
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
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test notification'), $model->data->hash);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));

        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications->data), 1);
        $this->assertEquals($notifications->data[0]->type, 'SystemNotificationModel');
        $this->assertEquals($notifications->data[0]->body->message, 'Test notification');
    }

    /**
     * @test
     */
    public function a_user_can_see_a_collection_of_notifications()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $model->data->hash);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications->data), 3);
        $this->assertEquals($notifications->data[0]->body->message, 'Test Notification 1');
        $this->assertEquals($notifications->data[1]->body->message, 'Test Notification 2');
        $this->assertEquals($notifications->data[2]->body->message, 'Test Notification 3');
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_a_notification()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);
        $generated = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);
        $generated2 = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);

        $this->call('DELETE', '/api/notifications/' . $generated->hash);
        $this->assertResponseStatus(200);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->data), 1);

        $this->call('DELETE', '/api/notifications/' . $generated2->hash);
        $this->assertResponseStatus(200);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications), 0);
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_one_out_of_many_notifications()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);
        $generated = $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $model->data->hash);

        $this->call('DELETE', '/api/notifications/' . $generated->hash);
        $this->assertResponseStatus(200);

        // double check to ensure notification is dismissed, but not others
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->data), 2);

        // these are NotificationContainerModel objects, so the property is ->notification to get the body
        $this->assertEquals($notifications->data[0]->body->message, 'Test Notification 1');
        $this->assertEquals($notifications->data[1]->body->message, 'Test Notification 3');

    }

    /**
     * @test
     */
    public function a_client_can_dismiss_all_notifications()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->data->hash);
        $m1 = $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);
        $m2 = $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $model->data->hash);
        $m3 = $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $model->data->hash);
        $internalCheck = $m1->notification;

         // make sure the notifications were inserted as expected; test would dud if this failed
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->data), 3);

        $this->call('POST', '/api/notifications/clear');
        $this->assertResponseStatus(200);

        // double-check notifications to ensure no undismissed ones are around
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
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
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->asUser($anotherModel->data->hash);

        $this->service->queue(SystemNotificationModel::make('Test Notification 1'), $model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 2'), $model->data->hash);
        $this->service->queue(SystemNotificationModel::make('Test Notification 3'), $model->data->hash);

        $this->service->queue(SystemNotificationModel::make('My Notification'), $anotherModel->data->hash);

        $request = $this->call('GET', '/api/notifications');
        $this->assertResponseStatus(200);
        $notifications = json_decode($request->getContent())->data;
        $this->assertEquals(1, count($notifications));
        $this->assertEquals('My Notification', $notifications[0]->body->message);

    }

    /**
     * @test
     */
    public function a_user_cannot_dismiss_other_user_notifications()
    {

        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->asUser($anotherModel->data->hash);
        $container = $this->service->queue(SystemNotificationModel::make('Test Notification'), $model->data->hash);

        $this->call('DELETE', '/api/notifications/' . $container->hash);
        $this->assertResponseStatus(403);


        // switch back to the owning user to test
        $this->asUser($model->data->hash);
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(1, count($notifications->data));
        $this->assertEquals('Test Notification', $notifications->data[0]->body->message);
    }
} 