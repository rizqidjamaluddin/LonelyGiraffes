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
        $notifications = $this->callJson('GET', '/api/notifications');

    }

    /**
     * @test
     */
    public function a_user_is_notified_when_a_request_is_accepted()
    {

    }
} 