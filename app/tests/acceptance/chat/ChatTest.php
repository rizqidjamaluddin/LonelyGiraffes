<?php

class ChatTest extends ChatCase
{


    /**
     * @test
     */
    public function users_can_make_a_chatroom()
    {
        $this->registerAndLoginAsMario();
        $create = $this->callJson('POST', '/api/chatrooms')->chatrooms[0];
        $this->assertResponseOk();

        $fetch = $this->callJson('GET', "/api/chatrooms/{$create->hash}")->chatrooms[0];
        $this->assertResponseOk();
        $this->assertChatroomUserCount($fetch, 1);

    }

    /**
     * @test
     */
    public function guests_cannot_create_chatrooms()
    {
        $this->asGuest();
        $this->callJson('POST', '/api/chatrooms');
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function a_chatroom_is_private_by_default()
    {
        $this->registerAndLoginAsMario();
        $create = $this->callJson('POST', '/api/chatrooms')->chatrooms[0];

        $this->registerAndLoginAsLuigi();
        $fetch = $this->callJson('GET', "/api/chatrooms/{$create->hash}");
        $this->assertResponseStatus(403);

        $this->asGuest();
        $fetch = $this->callJson('GET', "/api/chatrooms/{$create->hash}");
        $this->assertResponseStatus(401);
    }

    /**
     * Not yet tested - public rooms are a v2.1.0 feature
     */
    public function a_chatroom_can_be_public_and_users_can_join_them()
    {
        $this->registerAndLoginAsMario();
        $publish = $this->callJson('POST', "/api/chatrooms", ['public' => true]);
        $hash = $publish;
        $this->assertResponseOk();
        $this->assertEquals(count($publish->chatrooms[0]), 1);

        $this->registerAndLoginAsLuigi();
        $fetch = $this->callJson('GET', "/api/chatrooms/$hash")->chatrooms[0];
        // viewing the room automatically adds you to it
        $this->assertChatroomUserCount($fetch, 2);
        $this->assertResponseOk();

        $this->asGuest();
        $fetch = $this->callJson('GET', "/api/chatrooms/$hash");
        // guests don't count as actual users
        $this->assertChatroomUserCount($fetch, 2);
        $this->assertResponseOk();
    }

    /**
     * @test
     */
    public function users_can_see_the_rooms_they_are_in()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->callJson('POST', '/api/chatrooms');
        $this->callJson('POST', '/api/chatrooms');

        // add extra polluting chatrooms to make sure they're not listed
        $this->registerAndLoginAsLuigi();
        $this->callJson('POST', '/api/chatrooms');
        $this->callJson('POST', '/api/chatrooms');

        // get list
        $this->asUser($mario->hash);
        $list = $this->callJson('GET', '/api/chatrooms?participating');
        $this->assertResponseOk();
        $this->assertEquals(count($list->chatrooms), 2);
        $this->assertEquals($list->chatrooms[0]->participants[0]->user->name, 'Mario');
        $this->assertEquals($list->chatrooms[1]->participants[0]->user->name, 'Mario');

        // users with no chatrooms should just see a blank array
        $this->registerAndLoginAsYoshi();
        $list = $this->callJson('GET', '/api/chatrooms?participating');
        $this->assertResponseOk();
        $this->assertEquals(count($list->chatrooms), 0);

    }

    /**
     * @test
     */
    public function users_can_see_people_and_add_people_to_a_chatroom()
    {
        $mario = $this->registerAndLoginAsMario();
        $luigi = $this->registerLuigi();

        $room = $this->callJson('POST', '/api/chatrooms')->chatrooms[0];
        $this->assertResponseOk();
        $this->assertEquals(1, $room->participantCount);

        $this->callJson('POST', "/api/chatrooms/{$room->hash}/add", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertEquals(2, $room->participantCount);

        // prevent from double-adding user
    }

    public function users_can_receive_messages_in_a_chatroom()
    {

    }

    public function users_can_set_titles_to_chatrooms()
    {

    }

    public function users_can_leave_a_chatroom()
    {

    }

    /**
     * @param $fetch
     * @param $expectedUsers
     */
    protected function assertChatroomUserCount($fetch, $expectedUsers)
    {
        $this->assertEquals($expectedUsers, $fetch->participantCount);
        $this->assertEquals($expectedUsers, count($fetch->participants));
    }

} 