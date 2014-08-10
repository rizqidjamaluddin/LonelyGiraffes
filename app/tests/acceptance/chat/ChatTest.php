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

    public function users_can_see_the_rooms_they_are_in()
    {

    }

    public function users_can_see_people_and_add_people_to_a_chatroom()
    {

    }

    public function users_can_receive_messages_in_a_chatroom()
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