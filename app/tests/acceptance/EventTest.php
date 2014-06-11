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
        $response = $this->call('POST', 'api/events/', 
            [
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
            ]
        );
        $responseContent = json_decode($response->getContent());
        $this->assertResponseStatus(200);

        $this->assertEquals(1, $responseContent->event->user_id);
        $this->assertEquals('My Awesome Event', $responseContent->event->name);
        $this->assertEquals('Details of my awesome event', $responseContent->event->body);
        $this->assertEquals('Details of my awesome event', $responseContent->event->html_body);
        $this->assertEquals('http://www.google.com', $responseContent->event->url);
        $this->assertEquals('My Awesome Location', $responseContent->event->location);
        $this->assertEquals('Athens', $responseContent->event->city);
        $this->assertEquals('Georgia', $responseContent->event->state);
        $this->assertEquals('US', $responseContent->event->country);
        $this->assertEquals('', $responseContent->event->cell);
        $this->assertEquals('0000-00-00 00:00:00', $responseContent->event->timestamp);
    }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_find_an_event_by_hash()
     {
        $service = App::make('Giraffe\Events\EventService');
        $model = $service->createEvent(
            [
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
            ]
        );
        $response = $this->call('GET', 'api/events/' . $model->hash);
        $responseContent = json_decode($response->getContent());
        $this->assertResponseStatus(200);

        $this->assertEquals(1, $responseContent->event->user_id);
        $this->assertEquals('My Awesome Event', $responseContent->event->name);
        $this->assertEquals('Details of my awesome event', $responseContent->event->body);
        $this->assertEquals('Details of my awesome event', $responseContent->event->html_body);
        $this->assertEquals('http://www.google.com', $responseContent->event->url);
        $this->assertEquals('My Awesome Location', $responseContent->event->location);
        $this->assertEquals('Athens', $responseContent->event->city);
        $this->assertEquals('Georgia', $responseContent->event->state);
        $this->assertEquals('US', $responseContent->event->country);
        $this->assertEquals('', $responseContent->event->cell);
        $this->assertEquals('0000-00-00 00:00:00', $responseContent->event->timestamp);
     }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_delete_an_event_by_hash()
     {
        $service = App::make('Giraffe\Events\EventService');
        $model = $service->createEvent(
            [
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
            ]
        );
        $response = $this->call('DELETE', 'api/events/' . $model->hash);
        $this->assertResponseStatus(200);
     }

    /**
     * @depends it_can_create_a_new_event
     * @test
     */
     public function it_can_update_an_event_by_hash()
     {
        $service = App::make('Giraffe\Events\EventService');
        $model = $service->createEvent(
            [
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
            ]
        );
        $response = $this->call('PUT', 'api/events/' . $model->hash, 
            [
                'name'      => 'My Edited Awesome Event',
                'body'      => 'Details of my edited awesome event',
                'html_body' => 'Details of my edited awesome event',
                'url'       => 'http://www.notgoogle.com',
                'location'  => 'My Edited Awesome Location'
            ]
        );
        $responseContent = json_decode($response->getContent());
        $this->assertResponseStatus(200);

        $this->assertEquals('My Edited Awesome Event', $responseContent->event->name);
        $this->assertEquals('Details of my edited awesome event', $responseContent->event->body);
        $this->assertEquals('Details of my edited awesome event', $responseContent->event->html_body);
        $this->assertEquals('http://www.notgoogle.com', $responseContent->event->url);
        $this->assertEquals('My Edited Awesome Location', $responseContent->event->location);
     }
}