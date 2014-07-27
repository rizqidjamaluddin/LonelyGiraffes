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

        $fetch = $this->callJson('GET', "/api/chatrooms/{$create->hash}");
        $this->assertResponseOk();
        $this->assertChatroomUserCount($fetch, 1);

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
     * @test
     */
    public function a_chatroom_can_be_made_public_and_users_can_join_them()
    {
        $this->registerAndLoginAsMario();
        $create = $this->callJson('POST', '/api/chatrooms')->chatrooms[0];
        $hash = $create->hash;
        $this->assertResponseOk();

        // make public
        $publish = $this->callJson('POST', "/api/chatrooms/$hash/publish");
        $this->assertResponseOk();

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

    public function users_other_than_the_founder_cannot_publish_a_room()
    {

    }

    public function users_can_see_the_rooms_they_are_in()
    {

    }

    public function users_can_add_people_to_a_chatroom()
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
        $this->assertEquals($expectedUsers, $fetch->users);
    }

} 