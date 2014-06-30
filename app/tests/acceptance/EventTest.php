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

        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent))->event[0];
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

        $model = $this->toJson($this->call('GET', '/api/events/' . $model->hash))->event[0];
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
        $this->markTestIncomplete();

        $this->registerAndLoginAsMario();
        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent));

        $response = $this->call('DELETE', '/api/events/' . $model->event->hash);
        $this->assertResponseStatus(200);

        $check = $this->call('GET', '/api/events/' . $model->event->hash);
        $this->assertResponseStatus(404);
    }

    public function other_users_cannot_delete_an_event()
    {
        $this->markTestIncomplete();

        $this->registerAndLoginAsMario();
        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent));

        $this->registerAndLoginAsBowser();
        $response = $this->call('DELETE', '/api/events/' . $model->event->hash);
        $this->assertResponseStatus(403);

        $check = $this->call('GET', '/api/events/' . $model->event->hash);
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function it_can_update_an_event_by_hash()
    {
        $this->markTestIncomplete();
        $this->registerAndLoginAsMario();
        $model = $this->toJson($this->call('POST', '/api/events/', $this->genericEvent));
        $editEvent = $this->toJson(
            $this->call(
                'PUT',
                '/api/events/' . $model->event->hash,
                [
                    'name'     => 'My Edited Awesome Event',
                    'body'     => 'Details of my edited awesome event',
                    'url'      => 'http://www.notgoogle.com',
                    'location' => 'My Edited Awesome Location'
                ]
            )
        );
        $this->assertResponseStatus(200);

        $this->assertEquals('My Edited Awesome Event', $editEvent->event->name);
        $this->assertEquals('Details of my edited awesome event', $editEvent->event->body);
        $this->assertEquals('<p>Details of my edited awesome event</p>', $editEvent->event->html_body);
        $this->assertEquals('http://www.notgoogle.com', $editEvent->event->url);
        $this->assertEquals('My Edited Awesome Location', $editEvent->event->location);
    }
}