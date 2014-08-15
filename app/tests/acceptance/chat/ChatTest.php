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

        // make sure double-adding user doesn't break stuff
        $this->callJson('POST', "/api/chatrooms/{$room->hash}/add", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertEquals(2, $room->participantCount);
    }

    /**
     * @test
     */
    public function users_can_receive_messages_in_a_chatroom()
    {
        $luigi = $this->registerLuigi();
        $mario = $this->registerAndLoginAsMario();
        $room = $this->registerRoom()->hash;
        $this->callJson('POST', "/api/chatrooms/$room/add", ['user' => $luigi->hash]);

        $message = $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => 'Hello world!']);
        $this->assertResponseOk();

        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals($messages[0]->body, 'Hello world!');
        $this->assertEquals($messages[0]->author->name, 'Mario');

        // do the same thing as luigi
        $this->asUser($luigi->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals($messages[0]->body, 'Hello world!');
        $this->assertEquals($messages[0]->author->name, 'Mario');

        // test message as luigi
        $this->asUser($luigi->hash);
        $message = $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => 'Hey there!']);
        $this->assertResponseOk();

        // two more checks
        $this->asUser($mario->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals($messages[0]->body, 'Hello world!');
        $this->assertEquals($messages[0]->author->name, 'Mario');
        $this->assertEquals($messages[1]->body, 'Hey there!');
        $this->assertEquals($messages[1]->author->name, 'Luigi');

        $this->asUser($luigi->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals($messages[0]->body, 'Hello world!');
        $this->assertEquals($messages[0]->author->name, 'Mario');
        $this->assertEquals($messages[1]->body, 'Hey there!');
        $this->assertEquals($messages[1]->author->name, 'Luigi');

        // users not in the room can't make messages
        $bowser = $this->registerAndLoginAsBowser();

    }

    public function users_cannot_send_messages_over_280_characters_long()
    {

    }

    public function users_cannot_send_blank_messages()
    {

    }

    public function newly_added_users_cannot_see_messages_prior_to_joining()
    {

    }

    /**
     * @test
     */
    public function users_can_set_titles_to_chatrooms()
    {
        $mario = $this->registerAndLoginAsMario();
        $room = $this->registerRoom();

        // initial room title should be false
        $this->assertEquals(false, $room->title);

        // set title
        $this->callJson('PUT', "/api/chatrooms/{$room->hash}", ['title' => 'New Title']);

        // check new title
        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertResponseOk();
        $this->assertEquals('New Title', $room->title);

        // other people in the chat can change it too
        $luigi = $this->registerLuigi();
        $this->callJson('POST', "/api/chatrooms/{$room->hash}/add", ['user' => $luigi->hash]);
        $this->asUser($luigi->hash);
        $this->callJson('PUT', "/api/chatrooms/{$room->hash}", ['title' => 'Another New Title']);

        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertResponseOk();
        $this->assertEquals('Another New Title', $room->title);

        // people outside the chat can't change it
        $bowser = $this->registerAndLoginAsBowser();
        $this->callJson('PUT', "/api/chatrooms/{$room->hash}", ['title' => 'An Evil Title']);

        // back to mario because bowser can't even access the room
        $this->asUser($mario->hash);
        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertResponseOk();
        $this->assertEquals('Another New Title', $room->title);

        // other participants, however, are allowed
        $this->asUser($luigi->hash);
        $this->callJson('PUT', "/api/chatrooms/{$room->hash}", ['title' => 'Luigi Title']);
        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertResponseOk();
        $this->assertEquals('Luigi Title', $room->title);
    }

    public function users_can_leave_a_chatroom()
    {

    }

    public function users_can_be_removed_from_a_chatroom_by_other_users()
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

    /**
     * @return mixed
     */
    protected function registerRoom()
    {
        $room = $this->callJson('POST', '/api/chatrooms')->chatrooms[0];
        return $room;
    }

} 