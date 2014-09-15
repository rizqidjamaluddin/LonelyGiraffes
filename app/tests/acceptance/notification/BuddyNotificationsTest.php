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
        $this->callJson('POST', "/api/users/{$mario->hash}/buddy-requests");

        // check notification
        $this->asUser($mario->hash);
        $notifications = $this->callJson('GET', '/api/notifications');
        $this->assertResponseOk();
        $this->assertEquals(1, count($notifications->notifications));
        $response = $notifications->notifications[0];
        // verify various aspects of the response
        $this->assertEquals('new_buddy_request', $response->type);
        $this->assertEquals($this->mario['name'], $response->attached->request->recipient->name);
        $this->assertEquals($this->mario['email'], $response->attached->request->recipient->email);
        $this->assertEquals($this->luigi['name'], $response->attached->request->sender->name);
        $this->assertEquals($this->luigi['email'], $response->attached->request->sender->email);


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

    }
} 