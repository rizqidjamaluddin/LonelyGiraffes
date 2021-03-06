<?php

class ShoutTest extends AcceptanceCase
{
    protected $genericShout = ['body' => 'This is a shout!'];

    /**
     * @test
     */
    public function guests_cannot_post_shouts()
    {
        $this->call('POST', '/api/shouts', $this->genericShout);
        $this->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function users_can_post_shouts()
    {
        $this->registerAndLoginAsMario();
        $shout = $this->toJson($this->call('POST', '/api/shouts', $this->genericShout))->shouts[0];

        $this->assertResponseStatus(200);

        $getShout = $this->toJson($this->call('GET', "/api/shouts/{$shout->hash}"))->shouts[0];
        $this->assertEquals('This is a shout!', $getShout->body);
        $this->assertEquals($getShout->links->author->name, 'Mario');

        $this->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function a_user_cannot_post_a_shout_under_ten_characters_long()
    {
        $this->registerAndLoginAsMario();
        $this->call('POST', '/api/shouts', ['body' => 'Short']);
        $this->assertResponseStatus(422);

        $this->call('POST', '/api/shouts', ['body' => '']);
        $this->assertResponseStatus(422);

        $this->call('POST', '/api/shouts');
        $this->assertResponseStatus(422);
    }

    /**
     * @test
     */
    public function a_user_cannot_post_a_shout_over_a_thousand_characters_long()
    {
        // this test string is 1000 characters long.
        $longString = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla rhoncus dolor sit amet consequat ".
        "gravida. Mauris varius vulputate magna, at pellentesque augue sodales non. Fusce gravida nulla vel diam ".
        "suscipit, vitae aliquet ante mollis. Sed eleifend id mi adipiscing varius. Proin hendrerit volutpat justo quis ".
        "pretium. Praesent auctor blandit adipiscing. Suspendisse sollicitudin ipsum sit amet venenatis luctus. ".
        "Curabitur urna felis, volutpat eu molestie et, pharetra eget ipsum. Etiam vel quam pellentesque, congue tortor ".
        "id, hendrerit nulla. " .
        "Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In hac habitasse ".
        "platea dictumst. Donec tempor ligula sed sem fermentum, eget blandit velit ultrices. Sed orci mauris, varius ".
        "non neque vitae, dictum fermentum diam. Sed pellentesque nulla vitae ullamcorper gravida. Donec fermentum leo ".
        "non ultricies mollis. Vestibulum pellentesque aliquam mauris sit amet consequat. Duis quis quam porttitor ".
        "metus aliquet tristique.";

        $this->registerAndLoginAsMario();
        $this->call('POST', '/api/shouts', ['body' => $longString]);
        $this->assertResponseStatus(422);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_get_all_of_their_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $shoutBodies = [
            'This is a shout!',
            'This is also a shout!',
            'Another shout I am posting!',
            'Yay for shouts!'
        ];
        $this->asUser($model->users[0]->hash);

        for ($i = 0; $i < count($shoutBodies); $i++) {
            $this->call(
                 'POST',
                 '/api/shouts',
                 [
                     'body' => $shoutBodies[$i]
                 ]
            );
        }

        $getShouts = $this->toJson($this->call('GET', '/api/shouts?user=' . $model->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($shoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($shoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($shoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($shoutBodies[3], $getShouts->shouts[3]->body);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_get_all_of_another_users_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $shoutBodies = [
            'This is a shout!',
            'This is also a shout!',
            'Another shout I am posting!',
            'Yay for shouts!'
        ];
        $moreShoutBodies = [
            'This is a shout from another user!',
            'This is also a shout from another user!',
            'Another shout I am posting from another user!',
            'Yay for shouts from another user!'
        ];

        $this->asUser($model->users[0]->hash);

        for ($i = 0; $i < count($shoutBodies); $i++) {
            $this->call(
                 'POST',
                 '/api/shouts',
                 [
                     'body' => $shoutBodies[$i]
                 ]
            );
        }

        $this->asUser($anotherModel->users[0]->hash);

        $getShouts = $this->toJson($this->call('GET', '/api/shouts/user/' . $model->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($shoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($shoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($shoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($shoutBodies[3], $getShouts->shouts[3]->body);

        for ($i = 0; $i < count($moreShoutBodies); $i++) {
            $this->call(
                 'POST',
                 '/api/shouts',
                 [
                     'body' => $moreShoutBodies[$i]
                 ]
            );
        }

        $this->asUser($model->users[0]->hash);

        $getShouts = $this->toJson($this->call('GET', '/api/shouts/user/' . $anotherModel->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($moreShoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($moreShoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($moreShoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($moreShoutBodies[3], $getShouts->shouts[3]->body);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function a_user_cannot_delete_another_users_shout()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->asUser($model->users[0]->hash);

        $shout = $this->toJson(
                      $this->call(
                           'POST',
                           '/api/shouts',
                           [
                               'body' => 'This is a shout!'
                           ]
                      )
        );
        $shoutChild = $shout->shouts[0];

        $this->asUser($anotherModel->users[0]->hash);
        $deleteShout = $this->toJson($this->call('DELETE', '/api/shouts/' . $shoutChild->hash));
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_delete_specific_shouts()
    {
        // add some noise here to make sure other shouts don't get deleted
        $luigi = $this->registerAndLoginAsLuigi();
        $this->callJson('POST', '/api/shouts', ['body' => 'This shout should be intact']);
        $this->callJson('POST', '/api/shouts', ['body' => 'This shout should be intact']);

        $mario = $this->registerAndLoginAsMario();

        $this->callJson('POST', '/api/shouts', ['body' => 'This shout should be intact']);
        $shoutForDeletion = $this->callJson('POST', '/api/shouts', ['body' => 'Shout to be deleted']);
        $this->callJson('POST', '/api/shouts', ['body' => 'This shout should be intact']);

        $shoutChild = $shoutForDeletion->shouts[0];

        $deleteShout = $this->callJson('DELETE', "/api/shouts/{$shoutChild->hash}");
        $this->assertResponseStatus(200);

        $getShout = $this->toJson($this->call('GET', '/api/shouts/' . $shoutChild->hash));
        $this->assertResponseStatus(404);

        // deleting a shout should also delete its parent posts
        $posts = $this->callJson('GET', '/api/posts', ['user' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(2, count($posts->posts));
        $this->assertEquals('This shout should be intact', $posts->posts[0]->body->body);
        $this->assertEquals('This shout should be intact', $posts->posts[1]->body->body);
    }

    /**
     * @test
     */
    public function shouts_show_the_number_of_comments_they_have()
    {
        $mario = $this->registerAndLoginAsMario();
        $shout = $this->callJson('POST', '/api/shouts', ['body' => 'A shout with comments'])->shouts[0]->hash;

        $fetch = $this->callJson('GET', "/api/shouts/$shout");
        $this->assertResponseOk();
        $this->assertEquals(0, $fetch->shouts[0]->comment_count);

        $comment = $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 1"]);

        $fetch = $this->callJson('GET', "/api/shouts/$shout");
        $this->assertResponseOk();
        $this->assertEquals(1, $fetch->shouts[0]->comment_count);
    }

    /**
     * Similar to the above, except this is a small list of people participating in a shout comment thread
     * @test
     */
    public function shouts_have_comment_author_lists()
    {
        $mario = $this->registerAndLoginAsMario();
        $shout = $this->callJson('POST', '/api/shouts', ['body' => 'A shout with comments'])->shouts[0]->hash;

        $fetch = $this->callJson('GET', "/api/shouts/$shout");
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->shouts[0]->commentators));

        $comment = $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 1"]);
        $fetch = $this->callJson('GET', "/api/shouts/$shout");
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->shouts[0]->commentators));
        $this->assertEquals($this->mario['name'], $fetch->shouts[0]->commentators[0]->name);

        $luigi = $this->registerAndLoginAsLuigi();

        $comment = $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 1"]);
        $fetch = $this->callJson('GET', "/api/shouts/$shout");
        $this->assertResponseOk();
        $this->assertEquals(2, count($fetch->shouts[0]->commentators));
        $commentators = array_pluck($fetch->shouts[0]->commentators, 'name');
        $this->assertTrue(in_array($this->mario['name'], $commentators));
        $this->assertTrue(in_array($this->luigi['name'], $commentators));
    }
} 