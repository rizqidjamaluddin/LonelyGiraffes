<?php

use Giraffe\Notifications\NotificationRepository;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotification\SystemNotificationModel;
use Giraffe\Users\UserRepository;

class NotificationTest extends AcceptanceCase
{

    /**
     * @var NotificationRepository
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
        $this->containerRepository = App::make(NotificationRepository::class);
    }

    /**
     * @test
     */
    public function a_user_can_get_their_notifications_when_empty()
    {

        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));

        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications->notifications), 0);
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
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification']);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));

        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications->notifications), 1);
        $this->assertEquals($notifications->notifications[0]->type, 'system_notification');
        $this->assertEquals($notifications->notifications[0]->attached->message, 'Test Notification');
    }

    /**
     * @test
     */
    public function a_user_can_see_a_collection_of_notifications()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 1']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 2']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 3']);


        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertResponseStatus(200);
        $this->assertEquals(count($notifications->notifications), 3);
        $this->assertEquals($notifications->notifications[0]->attached->message, 'Test Notification 1');
        $this->assertEquals($notifications->notifications[1]->attached->message, 'Test Notification 2');
        $this->assertEquals($notifications->notifications[2]->attached->message, 'Test Notification 3');
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_a_notification()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 1']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 2']);

        $fetch = $this->callJson('GET', '/api/notifications');
        list($generated, $generated2) = [$fetch->notifications[0], $fetch->notifications[1]];

        $this->call('DELETE', '/api/notifications/' . $generated->hash);
        $this->assertResponseStatus(200);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->notifications), 1);

        $this->call('DELETE', '/api/notifications/' . $generated2->hash);
        $this->assertResponseStatus(200);

        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->notifications), 0);
    }

    /**
     * @test
     */
    public function a_client_can_dismiss_one_out_of_many_notifications()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 1']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 2']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 3']);

        $generated = $this->callJson('GET', '/api/notifications')->notifications[1];

        $this->call('DELETE', '/api/notifications/' . $generated->hash);
        $this->assertResponseStatus(200);

        // double check to ensure notification is dismissed, but not others
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->notifications), 2);

        // these are NotificationModel objects, so the property is ->notification to get the body
        $this->assertEquals($notifications->notifications[0]->attached->message, 'Test Notification 1');
        $this->assertEquals($notifications->notifications[1]->attached->message, 'Test Notification 3');

    }

    /**
     * @test
     */
    public function a_client_can_dismiss_all_notifications()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 1']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 2']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 3']);

        // make sure the notifications were inserted as expected; test would dud if this failed
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->notifications), 3);

        $this->call('POST', '/api/notifications/clear');
        $this->assertResponseStatus(200);

        // double-check notifications to ensure no undismissed ones are around
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(count($notifications->notifications), 0);

    }


    /**
     * @test
     */
    public function a_user_cannot_see_the_notifications_for_another_user()
    {
        $mario = $this->registerMario();
        $bowser = $this->registerAndLoginAsBowser();

        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 1']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 2']);
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification 3']);

        Artisan::call('lg:util:notify', ['hash' => $bowser->hash, 'body' => 'My Notification']);

        $request = $this->call('GET', '/api/notifications');
        $this->assertResponseStatus(200);
        $notifications = json_decode($request->getContent())->notifications;
        $this->assertEquals(1, count($notifications));
        $this->assertEquals('My Notification', $notifications[0]->attached->message);

    }

    /**
     * @test
     */
    public function a_user_cannot_dismiss_other_user_notifications()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification']);
        $hash = $this->callJson('GET', '/api/notifications')->notifications[0]->hash;

        $this->registerAndLoginAsBowser();

        $this->call('DELETE', "/api/notifications/$hash");
        $this->assertResponseStatus(403);

        // switch back to the owning user to test
        $this->asUser($mario->hash);
        $notifications = $this->toJson($this->call('GET', '/api/notifications'));
        $this->assertEquals(1, count($notifications->notifications));
        $this->assertEquals('Test Notification', $notifications->notifications[0]->attached->message);
    }

    /**
     * @test
     */
    public function a_client_can_filter_notifications_by_type()
    {
        $mario = $this->registerAndLoginAsMario();
        Artisan::call('lg:util:notify', ['hash' => $mario->hash, 'body' => 'Test Notification']);

        $fetch = $this->callJson('GET', '/api/notifications');
        $this->assertEquals(1, count($fetch->notifications));
        $this->assertEquals('Test Notification', $fetch->notifications[0]->attached->message);

        $fetch = $this->callJson('GET', '/api/notifications', ['only' => 'nonexistent_notification']);
        $this->assertEquals(0, count($fetch->notifications));

        $fetch = $this->callJson('GET', '/api/notifications', ['only' => 'system_notification']);
        $this->assertEquals(1, count($fetch->notifications));
        $this->assertEquals('Test Notification', $fetch->notifications[0]->attached->message);

        $fetch = $this->callJson('GET', '/api/notifications', ['except' => 'system_notification']);
        $this->assertEquals(0, count($fetch->notifications));


    }
} 