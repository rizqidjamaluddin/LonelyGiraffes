<?php

class FeedTest extends AcceptanceCase
{
    protected $genericShoutBody = 'Hello world, I am a shout!';
    protected $otherGenericShoutBody = "Hi, I'm a different kind of shout!";

    /**
     * @test
     */
    public function it_can_fetch_the_latest_few_posts_in_the_global_feed()
    {
        // add some test shouts
        $this->registerAndLoginAsMario();
        for ($i = 0; $i < 12; $i++) {
            $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        }

        // fetch them
        $results = $this->toJson($this->call('GET', '/api/posts'));

        $results = $results->posts;
        $this->assertEquals(count($results), 10);
        foreach ($results as $result) {
            $this->assertEquals($result->body->body, $this->genericShoutBody);
        }
    }

    /**
     * @test
     */
    public function it_can_fetch_posts_by_the_cursor_of_the_bottom_most_post()
    {
        $this->registerAndLoginAsMario();

        // these posts should be on the second chunk
        for ($i = 0; $i < 10; $i++) {
            $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        }
        // these posts should be the first to load
        for ($i = 0; $i < 10; $i++) {
            $this->call('POST', '/api/shouts', ['body' => $this->otherGenericShoutBody]);
        }

        $firstChunk = $this->toJson($this->call('GET', '/api/posts'));
        $this->assertResponseOk();
        $this->assertEquals($firstChunk->posts[0]->body->body, $this->otherGenericShoutBody);
        $this->assertEquals($firstChunk->posts[9]->body->body, $this->otherGenericShoutBody);
        $cursor = end($firstChunk->posts)->hash;

        $nextChunk = $this->toJson($this->call('GET', '/api/posts', ['before' => $cursor]));
        $this->assertResponseOk();
        $this->assertEquals($nextChunk->posts[0]->body->body, $this->genericShoutBody);
        $this->assertEquals($nextChunk->posts[9]->body->body, $this->genericShoutBody);
        $cursor = end($nextChunk->posts)->hash;

        $lastChunk = $this->toJson($this->call('GET', '/api/posts', ['before' => $cursor]));
        $this->assertResponseOk();
        $this->assertEquals(count($lastChunk->posts), 0);
    }



} 