<?php 
class CommentTest extends AcceptanceCase
{
    /**
     * @test
     */
    public function posts_can_have_comment_streams()
    {
        $luigi = $this->registerLuigi();
        $mario = $this->registerAndLoginAsMario();
        $shout = $this->callJson('POST', '/api/shouts', ['body' => 'This shout will have comments'])->shouts[0]->hash;

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->comments));

        // add one comment
        $create = $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 1"]);
        $this->assertResponseOk();
        $this->assertEquals('Comment 1', $create->comments[0]->body);
        $this->assertEquals($this->mario['name'], $create->comments[0]->links->author->name);

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->comments));
        $this->assertEquals("Comment 1", $fetch->comments[0]->body);
        $this->assertEquals($this->mario['name'], $fetch->comments[0]->links->author->name);

        // add a few more as luigi
        $this->asUser($luigi->hash);
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 2"]);
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 3"]);
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 4"]);
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 5"]);
        $this->assertResponseOk();

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(5, count($fetch->comments));
        $this->assertEquals("Comment 1", $fetch->comments[4]->body);
        $this->assertEquals($this->mario['name'], $fetch->comments[4]->links->author->name);
        $this->assertEquals("Comment 5", $fetch->comments[0]->body);
        $this->assertEquals($this->luigi['name'], $fetch->comments[0]->links->author->name);

        // empty comments are not valid
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => '']);
        $this->assertResponseStatus(422);

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(5, count($fetch->comments));

        // comments past 1000 characters long get truncated
        $longString = 'Comment 6 : Long!' . str_repeat('!', 1001);
        $truncatedLongString = substr('Comment 6 : Long!' . str_repeat('!', 1001), 0, 1000);
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => $longString]);
        $this->assertResponseOk();

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(6, count($fetch->comments));
        $this->assertEquals($truncatedLongString, $fetch->comments[0]->body);

        // guests can't comment
        $this->asGuest();
        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => '']);
        $this->assertResponseStatus(401);

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(6, count($fetch->comments));

    }

    /**
     * @test
     */
    public function comment_streams_can_be_navigated_with_before_and_after_params()
    {
        $mario = $this->registerAndLoginAsMario();
        $shout = $this->callJson('POST', '/api/shouts', ['body' => 'This shout will have comments'])->shouts[0]->hash;

        // add comments
        for ($i = 1; $i <= 50; $i++) {
            $create = $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment $i"]);
        }

        // default take is 10
        $baseFetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(10, count($baseFetch->comments));
        $this->assertEquals('Comment 50', $baseFetch->comments[0]->body);
        $this->assertEquals('Comment 41', $baseFetch->comments[9]->body);

        // max take is 20
        $baseFetch = $this->callJson('GET', "/api/shouts/$shout/comments", ['take' => 40]);
        $this->assertResponseOk();
        $this->assertEquals(20, count($baseFetch->comments));
        $this->assertEquals('Comment 50', $baseFetch->comments[0]->body);
        $this->assertEquals('Comment 31', $baseFetch->comments[19]->body);
        $bottom = $baseFetch->comments[19]->hash;

        // use with before
        $baseFetch = $this->callJson('GET', "/api/shouts/$shout/comments", ['before' => $bottom]);
        $this->assertResponseOk();
        $this->assertEquals(10, count($baseFetch->comments));
        $this->assertEquals('Comment 30', $baseFetch->comments[0]->body);
        $this->assertEquals('Comment 21', $baseFetch->comments[9]->body);

        // combine take and before
        $baseFetch = $this->callJson('GET', "/api/shouts/$shout/comments", ['before' => $bottom, 'take' => 15]);
        $this->assertResponseOk();
        $this->assertEquals(15, count($baseFetch->comments));
        $this->assertEquals('Comment 30', $baseFetch->comments[0]->body);
        $this->assertEquals('Comment 16', $baseFetch->comments[14]->body);

        // use with after
        // this will fetch comments 50-40, leaving a gap between 30 and 50
        $baseFetch = $this->callJson('GET', "/api/shouts/$shout/comments", ['after' => $bottom]);
        $this->assertResponseOk();
        $this->assertEquals(10, count($baseFetch->comments));
        $this->assertEquals('Comment 50', $baseFetch->comments[0]->body);
        $this->assertEquals('Comment 41', $baseFetch->comments[9]->body);
    }

    public function events_can_have_comment_streams()
    {

    }
} 