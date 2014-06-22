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

    public function setUp()
    {
        parent::setUp();
        $this->service = App::make('Giraffe\Events\EventService');
        $this->repository = App::make('Giraffe\Events\EventRepository');
    }

    /**
     * @test
     */
    public function it_can_create_a_new_event()
    {
        $model = $this->toJson($this->call('POST', '/api/events/', $this->event));
        $this->assertResponseStatus(200);

        $this->assertEquals(1, $model->event->user_id);
        $this->assertEquals('My Awesome Event', $model->event->name);
        $this->assertEquals('Details of my awesome event', $model->event->body);
        $this->assertEquals('Details of my awesome event', $model->event->html_body);
        $this->assertEquals('http://www.google.com', $model->event->url);
        $this->assertEquals('My Awesome Location', $model->event->location);
        $this->assertEquals('Athens', $model->event->city);
        $this->assertEquals('Georgia', $model->event->state);
        $this->assertEquals('US', $model->event->country);
        $this->assertEquals('', $model->event->cell);
        $this->assertEquals('0000-00-00 00:00:00', $model->event->timestamp);
    }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_find_an_event_by_hash()
     {
        $service = App::make('Giraffe\Events\EventService');
        $model = $this->toJson($this->call('POST', '/api/events/', $this->event));
        $getEvent = $this->toJson($this->call('GET', '/api/events/' . $model->event->hash));
        $this->assertResponseStatus(200);

        $this->assertEquals(1, $model->event->user_id);
        $this->assertEquals('My Awesome Event', $model->event->name);
        $this->assertEquals('Details of my awesome event', $model->event->body);
        $this->assertEquals('Details of my awesome event', $model->event->html_body);
        $this->assertEquals('http://www.google.com', $model->event->url);
        $this->assertEquals('My Awesome Location', $model->event->location);
        $this->assertEquals('Athens', $model->event->city);
        $this->assertEquals('Georgia', $model->event->state);
        $this->assertEquals('US', $model->event->country);
        $this->assertEquals('', $model->event->cell);
        $this->assertEquals('0000-00-00 00:00:00', $model->event->timestamp);
     }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_delete_an_event_by_hash()
     {
        $model = $this->toJson($this->call('POST', '/api/events/', $this->event));

        $response = $this->call('DELETE', '/api/events/' . $model->event->hash);
        $this->assertResponseStatus(200);
     }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_update_an_event_by_hash()
     {
        $model = $this->toJson($this->call('POST', '/api/events/', $this->event));
        $editEvent = $this->toJson($this->call('PUT', '/api/events/' . $model->event->hash, 
            [
                'name'      => 'My Edited Awesome Event',
                'body'      => 'Details of my edited awesome event',
                'html_body' => 'Details of my edited awesome event',
                'url'       => 'http://www.notgoogle.com',
                'location'  => 'My Edited Awesome Location'
            ]
        ));
        $this->assertResponseStatus(200);

        $this->assertEquals('My Edited Awesome Event', $editEvent->event->name);
        $this->assertEquals('Details of my edited awesome event', $editEvent->event->body);
        $this->assertEquals('Details of my edited awesome event', $editEvent->event->html_body);
        $this->assertEquals('http://www.notgoogle.com', $editEvent->event->url);
        $this->assertEquals('My Edited Awesome Location', $editEvent->event->location);
     }
}