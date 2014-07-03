<?php

class FeedTest extends AcceptanceCase
{

    /**
     * @test
     */
    public function it_can_fetch_the_latest_few_posts_in_the_global_feed()
    {
        // add some test shouts
        $this->registerAndLoginAsMario();
        for ($i = 0; $i < 12; $i++) {
            $this->call('POST', '/api/shouts', ['body' => 'Hello world, I am a shout!']);
        }

        // fetch them
        $results = $this->toJson($this->call('GET', '/api/posts'));

        $results = $results->posts;
        $this->assertEquals(count($results), 10);
        foreach ($results as $result) {
            $this->assertEquals($result->body->body, 'Hello world, I am a shout!');
        }
    }



} 