<?php

use Giraffe\Events\EventModel;
use Giraffe\Events\EventRepository;
use Giraffe\Events\EventService;

class EventCase extends AcceptanceCase
{
    /**
     * @var EventService
     */
    protected $service;

    /**
     * @var EventRepository
     */
    protected $repository;
    /**
     * @var array
     */
    protected $genericEvent = [
        'name'      => 'My Awesome Event',
        'body'      => 'Details of my awesome event',
        'url'       => 'http://www.google.com',
        'location'  => 'My Awesome Location',
        'timestamp' => '2014-12-25 00:00:00'
    ];

    protected $editedGenericEvent = [
        'name'     => 'My Edited Awesome Event',
        'body'     => 'Details of my edited awesome event',
        'url'      => 'http://www.notgoogle.com',
        'location' => 'My Edited Awesome Location'
    ];

    public function setUp()
    {
        parent::setUp();
        $this->service = App::make('Giraffe\Events\EventService');
        $this->repository = App::make('Giraffe\Events\EventRepository');
    }

    /**
     * @test
     */
    public function a_guest_cannot_create_an_event()
    {
        $this->call('POST', '/api/events/', $this->genericEvent);
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function a_user_can_create_a_new_event()
    {
        $this->registerAndLoginAsMario();

        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Awesome Event', $model->name);
        $this->assertEquals('Details of my awesome event', $model->body);
        $this->assertEquals('<p>Details of my awesome event</p>', $model->html_body);
        $this->assertEquals('http://www.google.com', $model->url);
        $this->assertEquals('My Awesome Location', $model->location);
        $this->assertEquals('2014-12-25 00:00:00', $model->timestamp);
        $this->assertEquals($model->links->owner->name, 'Mario');

        $model = $this->toJson($this->call('GET', '/api/events/' . $model->hash))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Awesome Event', $model->name);
        $this->assertEquals('Details of my awesome event', $model->body);
        $this->assertEquals('<p>Details of my awesome event</p>', $model->html_body);
        $this->assertEquals('http://www.google.com', $model->url);
        $this->assertEquals('My Awesome Location', $model->location);
        $this->assertEquals('2014-12-25 00:00:00', $model->timestamp);
        $this->assertEquals($model->links->owner->name, 'Mario');

    }

    /**
     * @test
     */
    public function a_user_can_delete_their_event_by_hash()
    {
        $this->registerAndLoginAsMario();
        $event = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];

        $response = $this->call('DELETE', '/api/events/' . $event->hash);
        $this->assertResponseStatus(200);

        $check = $this->call('GET', '/api/events/' . $event->hash);
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function deleting_an_event_also_deletes_the_post()
    {
        $this->registerAndLoginAsMario();
        $event = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];
        $post = $results = $this->toJson($this->call('GET', '/api/posts'));
        $target = $post->posts[0]->hash;

        $this->callJson('GET', "/api/posts/{$target}");
        $this->assertResponseOk();

        $this->call('DELETE', '/api/events/' . $event->hash);

        $this->callJson('GET', "/api/posts/{$target}");
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function other_users_cannot_delete_an_event()
    {

        $this->registerAndLoginAsMario();
        $event = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];

        $this->registerAndLoginAsBowser();
        $response = $this->call('DELETE', '/api/events/' . $event->hash);
        $this->assertResponseStatus(403);

        $check = $this->toJson($this->call('GET', '/api/events/' . $event->hash))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Awesome Event', $check->name);
    }

    /**
     * @test
     */
    public function the_owner_can_update_an_event_by_hash()
    {
        $mario = $this->registerAndLoginAsMario();
        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];

        /*
         * Evil edit as guest
         */

        $this->asGuest();
        $this->toJson( $this->call('PUT', '/api/events/' . $model->hash, $this->editedGenericEvent));
        $this->assertResponseStatus(401);


        $checkGuestEdit = $this->toJson( $this->call('GET', '/api/events/' . $model->hash))->events[0];
        $this->assertEquals('My Awesome Event', $checkGuestEdit->name);
        $this->assertEquals('Details of my awesome event', $checkGuestEdit->body);
        $this->assertEquals('<p>Details of my awesome event</p>', $checkGuestEdit->html_body);
        $this->assertEquals('http://www.google.com', $checkGuestEdit->url);
        $this->assertEquals('My Awesome Location', $checkGuestEdit->location);

        /*
         * Evil edit as bowser
         */

        $bowser = $this->registerAndLoginAsBowser();
        $evilEdit = $this->toJson( $this->call('PUT', '/api/events/' . $model->hash, $this->editedGenericEvent));
        $this->assertResponseStatus(403);


        $checkEvilEdit = $this->toJson( $this->call('GET', '/api/events/' . $model->hash))->events[0];
        $this->assertEquals('My Awesome Event', $checkEvilEdit->name);
        $this->assertEquals('Details of my awesome event', $checkEvilEdit->body);
        $this->assertEquals('<p>Details of my awesome event</p>', $checkEvilEdit->html_body);
        $this->assertEquals('http://www.google.com', $checkEvilEdit->url);
        $this->assertEquals('My Awesome Location', $checkEvilEdit->location);

        /*
         * Proper edit by user
         */

        $this->asUser($mario->hash);
        $editEvent = $this->toJson( $this->call('PUT', '/api/events/' . $model->hash, $this->editedGenericEvent))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Edited Awesome Event', $editEvent->name);
        $this->assertEquals('Details of my edited awesome event', $editEvent->body);
        $this->assertEquals('<p>Details of my edited awesome event</p>', $editEvent->html_body);
        $this->assertEquals('http://www.notgoogle.com', $editEvent->url);
        $this->assertEquals('My Edited Awesome Location', $editEvent->location);
    }

    /**
     * @test
     */
    public function timestamps_body_and_name_are_required()
    {


    }

    /**
     * @test
     */
    public function it_can_accept_a_variety_of_timestamp_formats()
    {
        $mario = $this->registerAndLoginAsMario();

        $event = $this->genericEvent;
        $event['timestamp'] = '2014-12-25 12:00:00';

        $model = $this->toJson($this->call('POST', '/api/events/', $event))->events[0];
        $this->assertEquals('2014-12-25 12:00:00', $model->timestamp);

        $event['timestamp'] = '2014-12-25T12:00:00+00:00';

        $model = $this->toJson($this->call('POST', '/api/events/', $event))->events[0];
        $this->assertEquals('2014-12-25 12:00:00', $model->timestamp);

        $event['timestamp'] = '2014-12-25T12:00:00+07:00';

        $model = $this->toJson($this->call('POST', '/api/events/', $event))->events[0];
        $this->assertEquals('2014-12-25 05:00:00', $model->timestamp);

        $event['timestamp'] = '2014-04-12T19:15:00.000Z';

        $model = $this->toJson($this->call('POST', '/api/events/', $event))->events[0];
        $this->assertEquals('2014-04-12 19:15:00', $model->timestamp);

    }


    /**
     * @test
     */
    public function invalid_dates_should_throw_an_error()
    {
        $mario = $this->registerAndLoginAsMario();

        $event = $this->genericEvent;
        $event['timestamp'] = '2014-13-35 12:00:00';

        $model = $this->toJson($this->call('POST', '/api/events/', $event));
        $this->assertResponseStatus(422);
    }
}