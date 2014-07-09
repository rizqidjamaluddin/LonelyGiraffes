<?php

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Buddies\BuddyRepository;
use Giraffe\Buddies\BuddyService;
use Json\Validator as JsonValidator;

class BuddyTest extends AcceptanceCase
{
    /**
     * @var BuddyService
     */
    protected $service;

    /**
     * @var BuddyRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->service = App::make('Giraffe\Buddies\BuddyService');
        $this->repository = App::make('Giraffe\Buddies\BuddyRepository');
    }

    /**
     * @test
     */
    public function it_can_fail_to_find_buddies()
    {
        $model = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->assertResponseStatus(200);

        $response = $this->call('GET', '/api/users/' . $model->users[0]->hash . '/buddies');
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function it_can_create_buddy_requests()
    {
        // Create users
        $model1 = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->assertResponseStatus(200);
        $model2 = $this->toJson($this->call("POST", "/api/users/", $this->anotherGenericUser));
        $this->assertResponseStatus(200);

        // Create buddy
        $response = $this->toJson($this->call("POST", "/api/users/" . $model1->users[0]->hash . "/buddies/requests",
                                            array('target' => $model2->users[0]->hash)
                                 ));
        $this->assertResponseStatus(200);

        $this->assertEquals('anotherHello@lonelygiraffes.com', $response->users[0]->email);
        $this->assertEquals('Lonesome Penguin', $response->users[0]->name);
        $this->assertEquals('F', $response->users[0]->gender);
    }

    //public function it_can_find_buddy_requests_initiated()
    // GET users/{resource}/buddies/requests

    //public function it_can_find_buddy_requests_received()
    // GET users/{resource}/buddies/requests

    ///public function it_can_find_accept_buddy_requests()
    // PUT users/{resource}/buddies/requests/{request}

    ///public function it_can_find_deny_buddy_requests()
    // DELETE users/{resource}/buddies/requests/{request}

    ///public function it_can_remove_buddies()
    // DELETE users/{resource}/buddies
}