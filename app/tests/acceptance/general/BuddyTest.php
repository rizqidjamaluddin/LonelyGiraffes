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
    public function it_can_create_and_find_buddy_requests()
    {
        // Create users
        $model1 = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->assertResponseStatus(200);
        $model2 = $this->toJson($this->call("POST", "/api/users/", $this->anotherGenericUser));
        $this->assertResponseStatus(200);
        $model3 = $this->toJson($this->call("POST", "/api/users/", $this->similarGenericUser));
        $this->assertResponseStatus(200);

        //////// Create the requests ////////

        // Create buddy request to anotherGenericUser
        $buddyRequest1 = $this->toJson($this->call("POST", "/api/users/" . $model1->users[0]->hash . "/buddies/requests",
                                  array('target' => $model2->users[0]->hash)));
        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $buddyRequest1->buddy_requests[0]->sender->email);
        $this->assertEquals('anotherHello@lonelygiraffes.com', $buddyRequest1->buddy_requests[0]->recipient->email);

        // Create buddy request to similarGenericUser
        $buddyRequest2 = $this->toJson($this->call("POST", "/api/users/" . $model1->users[0]->hash . "/buddies/requests",
            array('target' => $model3->users[0]->hash)));
        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $buddyRequest2->buddy_requests[0]->sender->email);
        $this->assertEquals('similarHello@lonelygiraffes.com', $buddyRequest2->buddy_requests[0]->recipient->email);

        //////// Check that they were sent ////////

        $getModels = $this->toJson($this->call("GET", "/api/users/" . $model1->users[0]->hash . "/buddies/requests",
                                   array('method' => 'sent')));
        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($getModels->buddy_requests));
        $this->assertEquals($buddyRequest1->buddy_requests[0]->sent_time, $getModels->buddy_requests[0]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[0]->sender->email);
        $this->assertEquals('anotherHello@lonelygiraffes.com', $getModels->buddy_requests[0]->recipient->email);
        $this->assertEquals($buddyRequest2->buddy_requests[0]->sent_time, $getModels->buddy_requests[1]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[1]->sender->email);
        $this->assertEquals('similarHello@lonelygiraffes.com', $getModels->buddy_requests[1]->recipient->email);

        //////// Check that they were received ////////

        // By anotherGenericUser
        $getModels = $this->toJson($this->call("GET", "/api/users/" . $model2->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->buddy_requests));
        $this->assertEquals($buddyRequest1->buddy_requests[0]->sent_time, $getModels->buddy_requests[0]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[0]->sender->email);
        $this->assertEquals('anotherHello@lonelygiraffes.com', $getModels->buddy_requests[0]->recipient->email);

        // By similarGenericUser
        $getModels = $this->toJson($this->call("GET", "/api/users/" . $model3->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->buddy_requests));
        $this->assertEquals($buddyRequest2->buddy_requests[0]->sent_time, $getModels->buddy_requests[0]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[0]->sender->email);
        $this->assertEquals('similarHello@lonelygiraffes.com', $getModels->buddy_requests[0]->recipient->email);

        return;
    }

    //public function it_can_find_buddy_requests_received()
    // GET users/{resource}/buddies/requests

    ///public function it_can_find_accept_buddy_requests()
    // PUT users/{resource}/buddies/requests/{request}

    ///public function it_can_find_deny_buddy_requests()
    // DELETE users/{resource}/buddies/requests/{request}

    ///public function it_can_remove_buddies()
    // DELETE users/{resource}/buddies
}