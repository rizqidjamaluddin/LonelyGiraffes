<?php

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Buddies\BuddyRepository;
use Giraffe\Buddies\BuddyService;
use Json\Validator as JsonValidator;

class BuddyTest extends AcceptanceCase
{

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
        $buddies = $this->callJson('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertResponseOk();
        $this->assertEquals(0, count($buddies->buddies));

        //////// Create the requests ////////

        // Create buddy request to luigi
        $buddyRequest1 = $this->callJson("POST", "/api/users/" . $luigi->hash . "/buddy-requests");
        $this->assertResponseStatus(200);
        $buddyRequest1 = $buddyRequest1->buddy_requests[0];
        $this->assertEquals($this->mario['name'], $buddyRequest1->sender->name);
        $this->assertEquals($this->luigi['name'], $buddyRequest1->recipient->name);

        // Create buddy request to yoshi
        $buddyRequest2 = $this->callJson("POST", "/api/users/" . $yoshi->hash . "/buddy-requests");
        $this->assertResponseStatus(200);
        $buddyRequest2 = $buddyRequest2->buddy_requests[0];
        $this->assertEquals($this->mario['name'], $buddyRequest2->sender->name);
        $this->assertEquals($this->yoshi['name'], $buddyRequest2->recipient->name);

        //////// Check that they were sent ////////

        $luigiRequests = $this->callJson("GET", "/api/users/" . $mario->hash . "/outgoing-buddy-requests");
        $this->assertResponseStatus(200);
        $this->assertEquals(2, count($luigiRequests->buddy_requests));
        $this->assertEquals($buddyRequest1->sent_timestamp, $luigiRequests->buddy_requests[0]->sent_timestamp);
        $this->assertEquals($this->mario['name'], $luigiRequests->buddy_requests[0]->sender->name);
        $this->assertEquals($this->luigi['name'], $luigiRequests->buddy_requests[0]->recipient->name);
        $this->assertEquals($buddyRequest2->sent_timestamp, $luigiRequests->buddy_requests[1]->sent_timestamp);
        $this->assertEquals($this->mario['name'], $luigiRequests->buddy_requests[1]->sender->name);
        $this->assertEquals($this->yoshi['name'], $luigiRequests->buddy_requests[1]->recipient->name);

        //////// Check that they were received ////////

        // By Luigi
        $this->asUser($luigi->hash);
        $luigiRequests = $this->toJson($this->call("GET", "/api/users/" . $luigi->hash . "/buddy-requests"));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($luigiRequests->buddy_requests));
        $this->assertEquals($buddyRequest1->sent_timestamp, $luigiRequests->buddy_requests[0]->sent_timestamp);
        $this->assertEquals($this->mario['name'], $luigiRequests->buddy_requests[0]->sender->name);
        $this->assertEquals($this->luigi['name'], $luigiRequests->buddy_requests[0]->recipient->name);

        // By Yoshi
        $this->asUser($yoshi->hash);
        $yoshiRequests = $this->toJson($this->call("GET", "/api/users/" . $yoshi->hash . "/buddy-requests"));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($yoshiRequests->buddy_requests));
        $this->assertEquals($buddyRequest2->sent_timestamp, $yoshiRequests->buddy_requests[0]->sent_timestamp);
        $this->assertEquals($this->mario['name'], $yoshiRequests->buddy_requests[0]->sender->name);
        $this->assertEquals($this->yoshi['name'], $yoshiRequests->buddy_requests[0]->recipient->name);

        //////// Accept a request ////////

        $this->asUser($luigi->hash);
        $accept = $this->toJson($this->call("POST", "/api/users/" . $luigi->hash . "/buddy-requests/"
                . $luigiRequests->buddy_requests[0]->hash . '/accept'));
        $this->assertResponseStatus(200);

        //////// Deny a request ////////

        $this->asUser($yoshi->hash);
        $this->toJson($this->call("DELETE", "/api/users/" . $yoshi->hash . "/buddy-requests/"
            . $yoshiRequests->buddy_requests[0]->hash));
        $this->assertResponseStatus(200);

        //////// Check that they both gone, for all parties ////////

        $this->asUser($mario->hash);
        $this->callJson("GET", "/api/users/" . $mario->hash . "/outgoing-buddy-requests");
        $this->assertResponseStatus(200);

        $this->asUser($luigi->hash);
        $this->callJson("GET", "/api/users/" . $luigi->hash . "/buddy-requests");
        $this->assertResponseStatus(200);

        $this->asUser($yoshi->hash);
        $this->callJson("GET", "/api/users/" . $yoshi->hash . "/buddy-requests");
        $this->assertResponseStatus(200);

        //////// Get Buddies ////////
        $this->asUser($mario->hash);
        $marioBuddies = $this->toJson($this->call('GET', '/api/users/' . $mario->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($marioBuddies->buddies));
        $this->assertEquals($this->luigi['name'], $marioBuddies->buddies[0]->name);

        $this->asUser($luigi->hash);
        $luigiBuddies = $this->toJson($this->call('GET', '/api/users/' . $luigi->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(1, count($luigiBuddies->buddies));
        $this->assertEquals($this->mario['name'], $luigiBuddies->buddies[0]->name);

        $this->asUser($yoshi->hash);
        $luigiBuddies = $this->toJson($this->call('GET', '/api/users/' . $yoshi->hash . '/buddies'));
        $this->assertResponseStatus(200);
        $this->assertEquals(0, count($luigiBuddies->buddies));

        //////// Remove a buddy ////////
        $this->asUser($luigi->hash);
        $response = $this->callJson('DELETE', '/api/users/' . $luigi->hash . '/buddies/' . $mario->hash);
        $this->assertResponseStatus(200);
        $this->assertEquals($response->message, "Mario is no longer listed as a buddy");

        //////// Fail to find buddies....again ////////
        $this->asUser($mario->hash);
        $marioBuddies = $this->callJson('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertEquals(0, count($marioBuddies->buddies));
        $this->assertResponseStatus(200);

        $this->asUser($luigi->hash);
        $luigiBuddies = $this->callJson('GET', '/api/users/' . $luigi->hash . '/buddies');
        $this->assertEquals(0, count($luigiBuddies->buddies));
        $this->assertResponseStatus(200);

        $this->asUser($yoshi->hash);
        $yoshiBuddies = $this->callJson('GET', '/api/users/' . $yoshi->hash . '/buddies');
        $this->assertEquals(0, count($yoshiBuddies->buddies));
        $this->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function a_user_cannot_see_another_users_buddies()
    {
        $mario = $this->registerMario();
        $buddies = $this->callJson('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertResponseStatus(401);

        $luigi = $this->registerAndLoginAsLuigi();
        $buddies = $this->callJson('GET', '/api/users/' . $mario->hash . '/buddies');
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     */
    public function a_guest_cannot_create_a_buddy_request()
    {
        $mario = $this->registerMario();
        $buddyRequest1 = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddy-requests");
        $this->assertResponseStatus(401);

        // make sure it wasn't actually added
        $this->asUser($mario->hash);
        $requests = $this->callJson('GET', '/api/users/' . $mario->hash . '/buddy-requests');
        $this->assertResponseOk();
        $this->assertEquals(0, count($requests->buddy_requests));
    }

    /**
     * @test
     */
    public function a_user_cannot_double_submit_a_buddy_request()
    {
        $mario = $this->registerAndLoginAsMario();
        $luigi = $this->registerAndLoginAsLuigi();
        $request = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddy-requests");
        $this->assertResponseOk();
        $request = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddy-requests");
        $this->assertResponseStatus(409);

        // check
        $this->asUser($mario->hash);
        $request = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertEquals(1, count($request->buddy_requests));

        // the opposite direction shouldn't work either
        $request = $this->callJson("POST", "/api/users/" . $luigi->hash . "/buddy-requests");
        $this->assertResponseStatus(409);

        // check
        $this->asUser($mario->hash);
        $request = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertEquals(1, count($request->buddy_requests));
        $this->asUser($luigi->hash);
        $request = $this->callJson('GET', "/api/users/{$luigi->hash}/outgoing-buddy-requests");
        $this->assertEquals(1, count($request->buddy_requests));


    }

    /**
     * @test
     */
    public function a_user_cannot_submit_a_buddy_request_for_an_existing_buddy()
    {
        $mario = $this->registerAndLoginAsMario();
        $luigi = $this->registerAndLoginAsLuigi();
        $request = $this->callJson("POST", "/api/users/" . $mario->hash . "/buddy-requests");
        $this->asUser($mario->hash);
        $accept = $this->callJson("POST", '/api/users/' . $mario->hash . '/buddy-requests/' . $request->buddy_requests[0]->hash . '/accept');
        $this->assertResponseOk();

        // try adding a duplicate request
        $this->asUser($luigi->hash);
        $this->callJson("POST", "/api/users/" . $mario->hash . "/buddy-requests");
        $this->assertResponseStatus(409);

        // check that the request didn't go through
        $this->asUser($mario->hash);
        $request = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertEquals(0, count($request->buddy_requests));
    }

    /**
     * @test
     */
    public function a_user_cannot_create_a_request_to_themself()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertResponseStatus(422);

        $request = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertEquals(0, count($request->buddy_requests));
    }

    /**
     * @test
     */
    public function a_user_cannot_see_other_users_requests()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerAndLoginAsLuigi();

        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertResponseOk();

        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertResponseStatus(403);

        $yoshi = $this->registerAndLoginAsYoshi();
        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertResponseStatus(403);

        $this->asGuest();
        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests");
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function a_user_cannot_see_other_users_buddies()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerAndLoginAsLuigi();
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests")->buddy_requests[0];
        $this->asUser($mario->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");

        $buddies = $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertEquals(1, count($buddies->buddies));

        $this->asUser($luigi->hash);
        $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseStatus(403);

        $this->registerAndLoginAsYoshi();
        $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseStatus(403);

        $this->asGuest();
        $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function a_user_cannot_accept_or_deny_a_request_not_for_them()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerAndLoginAsLuigi();
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests")->buddy_requests[0];

        $bowser = $this->registerAndLoginAsBowser();
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");
        $this->assertResponseStatus(403);
        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests/");
        $this->assertResponseStatus(403);

        // check
        $this->asUser($mario->hash);
        $buddies = $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseOk();
        $this->assertEquals(0, count($buddies->buddies));

        // try as guest
        $this->asGuest();
        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests/");
        $this->assertResponseStatus(401);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");
        $this->assertResponseStatus(401);

        // check
        $this->asUser($mario->hash);
        $buddies = $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseOk();
        $this->assertEquals(0, count($buddies->buddies));

        // try as luigi
        $this->asUser($luigi->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");
        $this->assertResponseStatus(403);
        $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests/");
        $this->assertResponseStatus(403);

        // check
        $this->asUser($mario->hash);
        $buddies = $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertResponseOk();
        $this->assertEquals(0, count($buddies->buddies));

    }

    /**
     * @test
     */
    public function a_user_cannot_delete_another_users_buddy()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();
        $this->establishBuddies($mario, $luigi);

        $this->registerAndLoginAsBowser();
        $this->callJson('DELETE', "/api/users/{$mario->hash}/buddies/{$luigi->hash}");
        $this->assertResponseStatus(403);

        $this->asGuest();
        $this->callJson('DELETE', "/api/users/{$mario->hash}/buddies/{$luigi->hash}");
        $this->assertResponseStatus(401);

        // make sure it was never deleted
        $this->asUser($mario->hash);
        $buddies = $this->callJson('GET', "/api/users/{$mario->hash}/buddies");
        $this->assertEquals(1, count($buddies->buddies));
    }

    /**
     * @test
     */
    public function users_can_see_their_buddy_status_on_the_user_profile()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();


        $this->asUser($mario->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$luigi->hash}");
        $this->assertResponseOk();
        $this->assertFalse(in_array('buddy', $endpoint->users[0]->relationships));

        $this->asUser($luigi->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertResponseOk();
        $this->assertFalse(in_array('buddy', $endpoint->users[0]->relationships));

        // send offer
        $this->asUser($luigi->hash);
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests")->buddy_requests[0];

        $this->asUser($mario->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$luigi->hash}");
        $this->assertResponseOk();
        $this->assertTrue(in_array('pending', $endpoint->users[0]->relationships));

        $this->asUser($luigi->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertResponseOk();
        $this->assertTrue(in_array('outgoing', $endpoint->users[0]->relationships));

        // accept
        $this->asUser($mario->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");

        $this->asUser($mario->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$luigi->hash}");
        $this->assertResponseOk();
        $this->assertTrue(in_array('buddy', $endpoint->users[0]->relationships));

        $this->asUser($luigi->hash);
        $endpoint = $this->callJson('GET', "/api/users/{$mario->hash}");
        $this->assertResponseOk();
        $this->assertTrue(in_array('buddy', $endpoint->users[0]->relationships));
    }

    /**
     * @test
     */
    public function clients_can_get_pending_requests_for_the_logged_in_user()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();
        $yoshi = $this->registerYoshi();

        // add a friend request from luigi to yoshi to ensure it doesn't pollute the lists
        $this->asUser($luigi->hash);
        $request = $this->callJson('POST', "/api/users/{$yoshi->hash}/buddy-requests");

        $this->asUser($mario->hash);
        $check = $this->callJson('GET', "/api/users/{$luigi->hash}/outgoing-buddy-requests", ['user' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($check->buddy_requests), 0);

        // send offer
        $this->asUser($luigi->hash);
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests")->buddy_requests[0];

        $this->asUser($mario->hash);
        $check = $this->callJson('GET', "/api/users/{$luigi->hash}/outgoing-buddy-requests", ['user' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($check->buddy_requests), 1);

        // accept
        $this->asUser($mario->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->hash}/accept");

        $this->asUser($mario->hash);
        $check = $this->callJson('GET', "/api/users/{$luigi->hash}/outgoing-buddy-requests", ['user' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($check->buddy_requests), 0);

        // make sure other users can't use this interface
        $this->registerAndLoginAsBowser();
        $check = $this->callJson('GET', "/api/users/{$luigi->hash}/outgoing-buddy-requests", ['user' => $mario->hash]);
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     */
    public function it_can_filter_buddy_lists_for_a_specific_user()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();
        $yoshi = $this->registerYoshi();

        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/buddies", ['user' => $luigi->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($fetch->buddies), 0);

        // make yoshi friends with yoshi
        $this->establishBuddies($mario, $yoshi);

        // make sure that the filter is working; this should not show yoshi
        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/buddies", ['user' => $luigi->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($fetch->buddies), 0);

        // make 'em buddies
        $this->establishBuddies($mario, $luigi);

        // should now contain luigi
        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/buddies", ['user' => $luigi->hash]);
        $this->assertResponseOk();
        $this->assertEquals(count($fetch->buddies), 1);
        $this->assertEquals($fetch->buddies[0]->name, $this->luigi['name']);

        // access should remain restricted
        $this->registerAndLoginAsBowser();
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/buddies", ['user' => $luigi->hash]);
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     */
    public function it_can_filter_buddy_requests_from_a_user()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();

        // make sure nobody else can do queries
        $this->registerAndLoginAsBowser();
        $check = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests", ['user' => $luigi->hash]);
        $this->assertResponseStatus(403);

        // make requests on behalf of both luigi and yoshi
        $this->asUser($luigi->hash);
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");
        $yoshi = $this->registerAndLoginAsYoshi();
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");

        // check that mario can filter for it
        $this->asUser($mario->hash);
        $check = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests", ['user' => $luigi->hash]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($check->buddy_requests));
        $this->assertEquals($this->luigi['name'], $check->buddy_requests[0]->sender->name);
        $this->assertEquals($this->mario['name'], $check->buddy_requests[0]->recipient->name);

        // check that yoshi can't try to use that same endpoint
        $this->asUser($yoshi->hash);
        $check = $this->callJson('GET', "/api/users/{$mario->hash}/buddy-requests", ['user' => $luigi->hash]);
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     * @see http://git.thinksterlabs.com/root/lonelygiraffes/issues/30
     *
     * This bug does not appear under SQLite, but does cause MySQL to break. Leaving test here
     * for future purposes.
     */
    public function client_sees_empty_collection_when_they_have_no_buddies()
    {
        $mario = $this->registerAndLoginAsMario()->hash;
        $buddies = $this->callJson('GET', "/api/users/$mario/buddies");
        $this->assertResponseOk();
        $this->assertEquals(0, count($buddies->buddies));
    }

    /**
     * @param $requested
     * @param $requester
     */
    protected function establishBuddies($requested, $requester)
    {
        $this->asUser($requester->hash);
        $request = $this->callJson('POST', "/api/users/{$requested->hash}/buddy-requests")->buddy_requests[0];
        $this->asUser($requested->hash);
        $this->callJson('POST', "/api/users/{$requested->hash}/buddy-requests/{$request->hash}/accept");
    }
}