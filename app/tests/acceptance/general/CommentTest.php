<?php 
class CommentTest extends AcceptanceCase
{
    /**
     * @test
     */
    public function posts_can_have_comment_streams()
    {
        $mario = $this->registerAndLoginAsMario();
        $shout = $this->callJson('POST', '/api/shouts', ['body' => 'This shout will have comments'])->shouts[0]->hash;

        $this->callJson('POST', "/api/shouts/$shout/comments", ['body' => "Comment 1"]);
        $this->assertResponseOk();

        $fetch = $this->callJson('GET', "/api/shouts/$shout/comments");
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->comments));
        $this->assertEquals("Comment 1", $fetch->comments[0]->body);

        // extra tests

        // empty comments are not valid

        // guests can't comment

    }

    public function comment_streams_can_be_navigated_with_before_and_after_params()
    {
        // default fetch is 10
    }

    public function events_can_have_comment_streams()
    {

    }
} 