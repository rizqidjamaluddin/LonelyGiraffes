<?php

use Giraffe\Users\EventModel;
use Giraffe\Users\EventRepository;
use Giraffe\Users\EventService;

class EventCase extends AcceptanceCase
{
    /**
     * @var UserService
     */
    protected $service;

    /**
     * @var UserRepository
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
        $response = $this->call('POST', 'api/events/', [
                'id' => 1
            ]
        );
        $this->assertResponseStatus(200);
    }
}