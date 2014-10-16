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
        $this->assertEquals(1, $room->participant_count);

        $this->callJson('POST', "/api/chatrooms/{$room->hash}/add", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertEquals(2, $room->participant_count);

        // make sure double-adding user doesn't break stuff
        $this->callJson('POST', "/api/chatrooms/{$room->hash}/add", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        $room = $this->callJson('GET', "/api/chatrooms/{$room->hash}")->chatrooms[0];
        $this->assertEquals(2, $room->participant_count);
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
        $this->assertEquals('Hello world!', $messages[0]->body);
        $this->assertEquals('Mario', $messages[0]->author->name);

        // do the same thing as luigi
        $this->asUser($luigi->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals('Hello world!', $messages[0]->body);
        $this->assertEquals('Mario', $messages[0]->author->name);

        // test message as luigi
        $this->asUser($luigi->hash);
        $message = $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => 'Hey there!']);
        $this->assertResponseOk();

        // two more checks
        $this->asUser($mario->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals('Hey there!', $messages[0]->body);
        $this->assertEquals('Luigi', $messages[0]->author->name);
        $this->assertEquals('Hello world!', $messages[1]->body);
        $this->assertEquals('Mario', $messages[1]->author->name);

        $this->asUser($luigi->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals('Hey there!', $messages[0]->body);
        $this->assertEquals('Luigi', $messages[0]->author->name);
        $this->assertEquals('Hello world!', $messages[1]->body);
        $this->assertEquals('Mario', $messages[1]->author->name);

        // users not in the room can't make messages
        $bowser = $this->registerAndLoginAsBowser();
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => 'Malicious content']);
        $this->assertResponseStatus(403);

        // make sure it was never sent
        $this->asUser($mario->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertEquals(2, count($messages));

        // empty messages are unacceptable
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => '']);
        $this->assertResponseStatus(422);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertEquals(2, count($messages));

        // character limit is 250 characters; any longer gets truncated
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => str_repeat('A', 300)]);
        $this->assertResponseStatus(200);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertEquals(3, count($messages));
        $this->assertEquals(str_repeat('A', 250), $messages[0]->body);

    }

    /**
     * @test
     */
    public function clients_can_use_before_and_after_parameters_to_navigate_messages()
    {
        $mario = $this->registerAndLoginAsMario();
        $room = $this->registerRoom()->hash;

        for ($i = 1; $i < 101; $i++) {
            $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Message $i"]);
        }

        // by default it should get the 30 latest ones
        $fetch = $this->callJson("GET", "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals(30, count($fetch));
        $this->assertEquals('Message 100', reset($fetch)->body);
        $this->assertEquals('Message 71', end($fetch)->body);

        // it can then get 30 further behind...
        $latest = reset($fetch)->hash;
        $oldest = end($fetch)->hash;
        $fetch = $this->callJson("GET", "/api/chatrooms/$room/messages", ['before' => $oldest])->messages;
        $this->assertResponseOk();
        $this->assertEquals(30, count($fetch));
        $this->assertEquals('Message 70', reset($fetch)->body);
        $this->assertEquals('Message 41', end($fetch)->body);

        // add a few new ones
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Message 101"]);
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Message 102"]);
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Message 103"]);

        // it can get the newest ones too
        $fetch = $this->callJson("GET", "/api/chatrooms/$room/messages", ['after' => $latest])->messages;
        $this->assertResponseOk();
        $this->assertEquals(3, count($fetch));
        $this->assertEquals('Message 103', reset($fetch)->body);
        $this->assertEquals('Message 101', end($fetch)->body);
    }

    /**
     * @test
     */
    public function clients_cannot_fetch_more_than_50_messages_at_once()
    {
        $mario = $this->registerAndLoginAsMario();
        $room = $this->registerRoom()->hash;
        for ($i = 1; $i < 101; $i++) {
            $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Message $i"]);
        }
        $fetch = $this->callJson("GET", "/api/chatrooms/$room/messages", ['take' => 100])->messages;
        $this->assertResponseOk();
        $this->assertEquals(50, count($fetch));
    }

    /**
     * @test
     */
    public function newly_added_users_cannot_see_messages_prior_to_joining()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();

        $this->asUser($mario->hash);
        $room = $this->registerRoom()->hash;

        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Secret Message 1"]);
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Secret Message 2"]);
        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Secret Message 3"]);

        // sleep 1 second to bump up a second; users can see messages up to the same second they joined
        sleep(1);

        $this->callJson('POST', "/api/chatrooms/$room/add", ['user' => $luigi->hash]);

        $this->asUser($luigi->hash);
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals(0, count($messages));

        $this->callJson('POST', "/api/chatrooms/$room/messages", ['message' => "Shared Message 1"]);
        $this->assertResponseOk();
        $messages = $this->callJson('GET', "/api/chatrooms/$room/messages")->messages;
        $this->assertEquals(1, count($messages));
        $this->assertEquals("Shared Message 1", $messages[0]->body);

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

    /**
     * @test
     */
    public function users_can_leave_a_chatroom()
    {
        $mario = $this->registerAndLoginAsMario();
        $luigi = $this->registerLuigi();

        $this->asUser($mario->hash);
        $room = $this->registerRoom()->hash;

        $this->callJson("POST", "/api/chatrooms/{$room}/add", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        $this->callJson('POST', "/api/chatrooms/{$room}/messages", ['message' => 'This is a message!']);
        $this->assertResponseOk();

        // only mario can make himself leave
        $this->asUser($mario->hash);
        $this->callJson('POST', "/api/chatrooms/$room/leave");
        $this->assertResponseOk();

        // check that mario can no longer query the chatroom
        $this->asUser($mario->hash);
        $this->callJson('GET', "/api/chatrooms/$room/messages");
        $this->assertResponseStatus(403);
        $this->callJson('GET', "/api/chatrooms/$room");
        $this->assertResponseStatus(403);

        // check that luigi still can
        $this->asUser($luigi->hash);
        $messages = $this->callJson("GET", "/api/chatrooms/$room/messages")->messages;
        $this->assertResponseOk();
        $this->assertEquals(1, count($messages));

        // check that luigi sees a room to himself
        $participants = $this->callJson('GET', "/api/chatrooms/$room")->chatrooms[0]->participants;
        $this->assertResponseOk();
        $this->assertEquals(1, count($participants));
        $this->assertEquals($luigi->name, $participants[0]->user->name);

    }

    /**
     * @test
     */
    public function users_can_be_removed_from_a_chatroom_by_other_participants()
    {
        $mario = $this->registerAndLoginAsMario();
        $luigi = $this->registerLuigi();
        $room = $this->registerRoom()->hash;
        $this->callJson("POST", "/api/chatrooms/{$room}/add", ['user' => $luigi->hash]);
        $this->callJson('POST', "/api/chatrooms/{$room}/messages", ['message' => 'This is a message!']);

        // bowser or guest can't remove luigi
        $this->registerAndLoginAsBowser();
        $this->callJson("POST", "/api/chatrooms/{$room}/kick", ['user' => $luigi->hash]);
        $this->assertResponseStatus(403);
        $this->asGuest();
        $this->callJson("POST", "/api/chatrooms/{$room}/kick", ['user' => $luigi->hash]);
        $this->assertResponseStatus(401);

        // mario can kick luigi out
        $this->asUser($mario->hash);
        $kick = $this->callJson("POST", "/api/chatrooms/{$room}/kick", ['user' => $luigi->hash]);
        $this->assertResponseOk();

        // check participants count
        $participants = $this->callJson('GET', "/api/chatrooms/{$room}")->chatrooms[0]->participants;
        $this->assertResponseOk();
        $this->assertEquals(1, count($participants));

        // luigi no longer has access
        $this->asUser($luigi->hash);
        $this->callJson('GET', "/api/chatrooms/{$room}");
        $this->assertResponseStatus(403);
    }

    /**
     * @param $fetch
     * @param $expectedUsers
     */
    protected function assertChatroomUserCount($fetch, $expectedUsers)
    {
        $this->assertEquals($expectedUsers, $fetch->participant_count);
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