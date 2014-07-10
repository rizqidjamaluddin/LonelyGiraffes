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
    public function it_can_create_and_find_buddy_requests()
    {
        // Create users
        $sender = $this->toJson($this->call("POST", "/api/users/", $this->genericUser));
        $this->assertResponseStatus(200);
        $receiver1 = $this->toJson($this->call("POST", "/api/users/", $this->anotherGenericUser));
        $this->assertResponseStatus(200);
        $receiver2 = $this->toJson($this->call("POST", "/api/users/", $this->similarGenericUser));
        $this->assertResponseStatus(200);

        //////// Fail to find buddies ////////
        $this->call('GET', '/api/users/' . $sender->users[0]->hash . '/buddies');
        $this->assertResponseStatus(404);

        //////// Create the requests ////////

        // Create buddy request to anotherGenericUser
        $buddyRequest1 = $this->toJson($this->call("POST", "/api/users/" . $sender->users[0]->hash . "/buddies/requests",
                                  array('target' => $receiver1->users[0]->hash)));
        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $buddyRequest1->buddy_requests[0]->sender->email);
        $this->assertEquals('anotherHello@lonelygiraffes.com', $buddyRequest1->buddy_requests[0]->recipient->email);

        // Create buddy request to similarGenericUser
        $buddyRequest2 = $this->toJson($this->call("POST", "/api/users/" . $sender->users[0]->hash . "/buddies/requests",
            array('target' => $receiver2->users[0]->hash)));
        $this->assertResponseStatus(200);
        $this->assertEquals('hello@lonelygiraffes.com', $buddyRequest2->buddy_requests[0]->sender->email);
        $this->assertEquals('similarHello@lonelygiraffes.com', $buddyRequest2->buddy_requests[0]->recipient->email);

        //////// Check that they were sent ////////

        $getModels = $this->toJson($this->call("GET", "/api/users/" . $sender->users[0]->hash . "/buddies/requests",
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
        $getModels = $this->toJson($this->call("GET", "/api/users/" . $receiver1->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->buddy_requests));
        $this->assertEquals($buddyRequest1->buddy_requests[0]->sent_time, $getModels->buddy_requests[0]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[0]->sender->email);
        $this->assertEquals('anotherHello@lonelygiraffes.com', $getModels->buddy_requests[0]->recipient->email);

        // By similarGenericUser
        $getModels = $this->toJson($this->call("GET", "/api/users/" . $receiver2->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->buddy_requests));
        $this->assertEquals($buddyRequest2->buddy_requests[0]->sent_time, $getModels->buddy_requests[0]->sent_time);
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->buddy_requests[0]->sender->email);
        $this->assertEquals('similarHello@lonelygiraffes.com', $getModels->buddy_requests[0]->recipient->email);

        //////// Accept a request ////////

        $getModels = $this->toJson($this->call("PUT", "/api/users/" . $receiver1->users[0]->hash . "/buddies/requests/" . $sender->users[0]->hash));
        $users = array($getModels->users[0]->email, $getModels->users[1]->email);
        sort($users);

        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($getModels->users));
        $this->assertEquals('anotherHello@lonelygiraffes.com', $users[0]);
        $this->assertEquals('hello@lonelygiraffes.com', $users[1]);


        //////// Deny a request ////////

        $this->toJson($this->call("DELETE", "/api/users/" . $receiver2->users[0]->hash . "/buddies/requests/" . $sender->users[0]->hash));
        $this->assertResponseStatus(200);

        //////// Check that they both gone, for all parties ////////

        $this->toJson($this->call("GET", "/api/users/" . $sender->users[0]->hash . "/buddies/requests",
            array('method' => 'sent')));
        $this->assertResponseStatus(404);

        $this->toJson($this->call("GET", "/api/users/" . $receiver1->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(404);

        $this->toJson($this->call("GET", "/api/users/" . $receiver2->users[0]->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(404);

        //////// Get Buddies ////////
        $getModels = $this->toJson($this->call('GET', '/api/users/' . $sender->users[0]->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->users));
        $this->assertEquals('anotherHello@lonelygiraffes.com', $getModels->users[0]->email);

        $getModels = $this->toJson($this->call('GET', '/api/users/' . $receiver1->users[0]->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($getModels->users));
        $this->assertEquals('hello@lonelygiraffes.com', $getModels->users[0]->email);

        //////// Remove a buddy ////////
        $this->call('DELETE', '/api/users/' . $receiver1->users[0]->hash . '/buddies',
            array('target' => $sender->users[0]->hash));
        $this->assertResponseStatus(200);

        //////// Fail to find buddies....again ////////
        $this->call('GET', '/api/users/' . $sender->users[0]->hash . '/buddies');
        $this->assertResponseStatus(404);
        $this->call('GET', '/api/users/' . $receiver1->users[0]->hash . '/buddies');
        $this->assertResponseStatus(404);
        $this->call('GET', '/api/users/' . $receiver2->users[0]->hash . '/buddies');
        $this->assertResponseStatus(404);
    }
}