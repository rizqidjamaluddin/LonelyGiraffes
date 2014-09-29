<?php

class BuddyNotificationsTest extends AcceptanceCase
{
    /**
     * @test
     */
    public function a_user_is_notified_when_receiving_a_buddy_request()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerAndLoginAsLuigi();

        // execute request
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");

        // check notification
        $this->asUser($mario->hash);
        $notifications = $this->callJson('GET', '/api/notifications');
        $this->assertResponseOk();
        $this->assertEquals(1, count($notifications->notifications));
        $response = $notifications->notifications[0];
        // verify various aspects of the response
        $this->assertEquals('new_buddy_request', $response->type);
        $this->assertEquals('Luigi sent you a buddy request!', $response->body);
        $this->assertEquals($this->luigi['name'], $response->links->sender->name);
        $this->assertEquals($this->luigi['email'], $response->links->sender->email);

        // actions should be available to accept/deny the request
        $this->assertEquals(2, count((array) $response->actions));
        $this->assertEquals("Accept", $response->actions->accept->label);
        $this->assertTrue(strpos($response->actions->accept->url, "/api/users/{$mario->hash}/buddy-requests/{$request->buddy_requests[0]->hash}/accept") !== false);
        $this->assertEquals("POST", $response->actions->accept->method);
        $this->assertEquals("Deny", $response->actions->deny->label);
        $this->assertTrue(strpos($response->actions->deny->url, "/api/users/{$mario->hash}/buddy-requests/{$request->buddy_requests[0]->hash}") !== false);
        $this->assertEquals("DELETE", $response->actions->deny->method);


        // and the type should be new_buddy_request, filterable
        $notifications = $this->callJson('GET', '/api/notifications', ['only' => 'new_buddy_request']);
        $this->assertResponseOk();
        $this->assertEquals(1, count($notifications->notifications));

        // luigi shouldn't have a notification
        $this->asUser($luigi->hash);
        $notifications = $this->callJson('GET', '/api/notifications');
        $this->assertResponseOk();
        $this->assertEquals(0, count($notifications->notifications));
    }

    /**
     * @test
     */
    public function the_sender_is_notified_when_a_request_is_accepted()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerAndLoginAsLuigi();

        // execute request
        $request = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");

        // accept it
        $this->asUser($mario->hash);
        $accept = $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests/{$request->buddy_requests[0]->hash}/accept");

        // mario shouldn't have a notification
        $notifications = $this->callJson('GET', '/api/notifications');
        $this->assertResponseOk();
        $this->assertEquals(0, count($notifications->notifications));

    }

    public function a_buddy_request_notification_disappears_if_the_sender_account_is_deleted()
    {

    }
} 