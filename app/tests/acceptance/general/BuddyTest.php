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
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();
        $yoshi = $this->registerYoshi();

        $this->asUser($mario->hash);

        //////// Fail to find buddies ////////
        $this->call('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertResponseStatus(404);

        //////// Create the requests ////////

        // Create buddy request to luigi
        $buddyRequest1 = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddies/requests",
                                  ['target' => $luigi->hash])->buddy_requests[0];
        $this->assertResponseStatus(200);
        $this->assertEquals($this->mario['email'], $buddyRequest1->sender->email);
        $this->assertEquals($this->luigi['email'], $buddyRequest1->recipient->email);

        // Create buddy request to yoshi
        $buddyRequest2 = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddies/requests",
                                         ['target' => $yoshi->hash])->buddy_requests[0];
        $this->assertResponseStatus(200);
        $this->assertEquals($this->mario['email'], $buddyRequest2->sender->email);
        $this->assertEquals($this->yoshi['email'], $buddyRequest2->recipient->email);

        //////// Check that they were sent ////////

        $luigiRequests = $this->toJson($this->call("GET", "/api/users/" . $mario->hash . "/buddies/requests",
                                   ['method' => 'sent']));
        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($luigiRequests->buddy_requests));
        $this->assertEquals($buddyRequest1->sent_time, $luigiRequests->buddy_requests[0]->sent_time);
        $this->assertEquals($this->mario['email'], $luigiRequests->buddy_requests[0]->sender->email);
        $this->assertEquals($this->luigi['email'], $luigiRequests->buddy_requests[0]->recipient->email);
        $this->assertEquals($buddyRequest2->sent_time, $luigiRequests->buddy_requests[1]->sent_time);
        $this->assertEquals($this->mario['email'], $luigiRequests->buddy_requests[1]->sender->email);
        $this->assertEquals($this->yoshi['email'], $luigiRequests->buddy_requests[1]->recipient->email);

        //////// Check that they were received ////////

        // By Luigi
        $luigiRequests = $this->toJson($this->call("GET", "/api/users/" . $luigi->hash . "/buddies/requests",
            ['method' => 'received']));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($luigiRequests->buddy_requests));
        $this->assertEquals($buddyRequest1->sent_time, $luigiRequests->buddy_requests[0]->sent_time);
        $this->assertEquals($this->mario['email'], $luigiRequests->buddy_requests[0]->sender->email);
        $this->assertEquals($this->luigi['email'], $luigiRequests->buddy_requests[0]->recipient->email);

        // By Yoshi
        $yoshiRequests = $this->toJson($this->call("GET", "/api/users/" . $yoshi->hash . "/buddies/requests",
            ['method' => 'received']));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($yoshiRequests->buddy_requests));
        $this->assertEquals($buddyRequest2->sent_time, $yoshiRequests->buddy_requests[0]->sent_time);
        $this->assertEquals($this->mario['email'], $yoshiRequests->buddy_requests[0]->sender->email);
        $this->assertEquals($this->yoshi['email'], $yoshiRequests->buddy_requests[0]->recipient->email);

        //////// Accept a request ////////

        $accept = $this->toJson($this->call("PUT", "/api/users/" . $luigi->hash . "/buddies/requests/" . $mario->hash));
        $users = array($accept->users[0]->email, $accept->users[1]->email);
        sort($users);

        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($accept->users));
        $this->assertEquals($this->luigi['email'], $users[0]);
        $this->assertEquals($this->mario['email'], $users[1]);


        //////// Deny a request ////////

        $this->toJson($this->call("DELETE", "/api/users/" . $yoshi->hash . "/buddies/requests/" . $mario->hash));
        $this->assertResponseStatus(200);

        //////// Check that they both gone, for all parties ////////

        $this->toJson($this->call("GET", "/api/users/" . $mario->hash . "/buddies/requests",
            array('method' => 'sent')));
        $this->assertResponseStatus(404);

        $this->toJson($this->call("GET", "/api/users/" . $luigi->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(404);

        $this->toJson($this->call("GET", "/api/users/" . $yoshi->hash . "/buddies/requests",
            array('method' => 'received')));
        $this->assertResponseStatus(404);

        //////// Get Buddies ////////
        $marioBuddies = $this->toJson($this->call('GET', '/api/users/' . $mario->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($marioBuddies->users));
        $this->assertEquals($this->luigi['email'], $marioBuddies->users[0]->email);

        $luigiBuddies = $this->toJson($this->call('GET', '/api/users/' . $luigi->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($luigiBuddies->users));
        $this->assertEquals($this->mario['email'], $luigiBuddies->users[0]->email);

        //////// Remove a buddy ////////
        $this->call('DELETE', '/api/users/' . $luigi->hash . '/buddies',
            array('target' => $mario->hash));
        $this->assertResponseStatus(200);

        //////// Fail to find buddies....again ////////
        $this->call('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertResponseStatus(404);
        $this->call('GET', '/api/users/' . $luigi->hash . '/buddies');
        $this->assertResponseStatus(404);
        $this->call('GET', '/api/users/' . $yoshi->hash . '/buddies');
        $this->assertResponseStatus(404);
    }

    public function a_user_cannot_create_a_request_on_behalf_of_another_user()
    {

    }

    public function a_user_cannot_see_other_users_requests()
    {

    }

    public function a_user_cannot_accept_a_request_not_for_them()
    {

    }
}