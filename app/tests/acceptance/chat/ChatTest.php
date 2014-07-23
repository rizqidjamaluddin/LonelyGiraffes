<?php

class ChatTest extends ChatCase
{


    /**
     * @test
     */
    public function users_can_make_a_chatroom()
    {
        $create = $this->callJson('POST', '/api/chatrooms');
        $this->assertResponseOk();
    }

    public function a_chatroom_can_be_made_public()
    {

    }

    public function users_can_join_a_public_chatroom()
    {

    }

    public function users_can_add_buddies_to_a_chatroom()
    {

    }

    public function users_can_receive_messages_in_a_chatroom()
    {
        
    }

    public function users_can_leave_a_chatroom()
    {

    }

} 