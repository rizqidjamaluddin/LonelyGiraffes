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
        'user_id'   => 1,
        'name'      => 'My Awesome Event',
        'body'      => 'Details of my awesome event',
        'html_body' => 'Details of my awesome event',
        'url'       => 'http://www.google.com',
        'location'  => 'My Awesome Location',
        'city'      => 'Athens',
        'state'     => 'Georgia',
        'country'   => 'US',
        'cell'      => '',
        'timestamp' => '0000-00-00 00:00:00'
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
        $this->assertEquals('Athens', $model->city);
        $this->assertEquals('Georgia', $model->state);
        $this->assertEquals('US', $model->country);
        $this->assertEquals('0000-00-00 00:00:00', $model->timestamp);

        $model = $this->toJson($this->call('GET', '/api/events/' . $model->hash))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Awesome Event', $model->name);
        $this->assertEquals('Details of my awesome event', $model->body);
        $this->assertEquals('<p>Details of my awesome event</p>', $model->html_body);
        $this->assertEquals('http://www.google.com', $model->url);
        $this->assertEquals('My Awesome Location', $model->location);
        $this->assertEquals('Athens', $model->city);
        $this->assertEquals('Georgia', $model->state);
        $this->assertEquals('US', $model->country);
        $this->assertEquals('0000-00-00 00:00:00', $model->timestamp);
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
        $this->registerAndLoginAsMario();
        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->events[0];
        $editEvent = $this->toJson( $this->call('PUT', '/api/events/' . $model->hash, $this->editedGenericEvent))->events[0];
        $this->assertResponseStatus(200);

        $this->assertEquals('My Edited Awesome Event', $editEvent->name);
        $this->assertEquals('Details of my edited awesome event', $editEvent->body);
        $this->assertEquals('<p>Details of my edited awesome event</p>', $editEvent->html_body);
        $this->assertEquals('http://www.notgoogle.com', $editEvent->url);
        $this->assertEquals('My Edited Awesome Location', $editEvent->location);
    }
}