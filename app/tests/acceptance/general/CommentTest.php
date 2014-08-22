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
        $this->assertEquals($this->mario['name'], $create->comments[0]->author->name);

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->comments));
        $this->assertEquals("Comment 1", $fetch->comments[0]->body);
        $this->assertEquals($this->mario['name'], $fetch->comments[0]->author->name);

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
        $this->assertEquals($this->mario['name'], $fetch->comments[4]->author->name);
        $this->assertEquals("Comment 5", $fetch->comments[0]->body);
        $this->assertEquals($this->luigi['name'], $fetch->comments[0]->author->name);

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

    public function comments_cannot_be_added_to_nonexistant_shouts()
    {

    }

    public function comment_streams_can_be_navigated_with_before_and_after_params()
    {
        // default fetch is 10
    }

    public function events_can_have_comment_streams()
    {

    }
} 