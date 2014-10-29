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
        $this->assertEquals(10, count($results));
        foreach ($results as $result) {
            $this->assertEquals($result->body->body, $this->genericShoutBody);
        }
    }

    /**
     * @test
     */
    public function it_can_fetch_a_single_post()
    {
        $mario = $this->registerAndLoginAsMario();
        $insert = $this->toJson($this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]));
        $this->assertResponseOk();

        // get the feed, because the post data itself isn't in $insert, which was a shout resource
        $feed = $this->toJson($this->call('GET', '/api/posts'));
        $hash = $feed->posts[0]->hash;

        $fetch = $this->toJson($this->call('GET', '/api/posts/' . $hash));
        $this->assertResponseOk();
        $this->assertEquals($this->genericShoutBody, $fetch->posts[0]->body->body);
        $this->assertEquals($mario->hash, $fetch->posts[0]->links->author->hash);

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

        // test before
        $nextChunk = $this->toJson($this->call('GET', '/api/posts', ['before' => $cursor]));
        $this->assertResponseOk();
        $this->assertEquals($nextChunk->posts[0]->body->body, $this->genericShoutBody);
        $this->assertEquals($nextChunk->posts[9]->body->body, $this->genericShoutBody);
        $cursor = end($nextChunk->posts)->hash;
        $lastChunk = $this->toJson($this->call('GET', '/api/posts', ['before' => $cursor]));
        $this->assertResponseOk();
        $this->assertEquals(count($lastChunk->posts), 0);
    }

    /**
     * @test
     */
    public function it_can_find_posts_by_the_cursor_off_the_top_post()
    {
        $this->registerAndLoginAsMario();

        // initial stuff
        for ($i = 0; $i < 10; $i++) {
            $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        }

        $stuff = $this->toJson($this->call('GET', '/api/posts'))->posts;
        $this->assertResponseOk();
        $this->assertEquals(count($stuff), 10);
        $topCursor = $stuff[0]->hash;

        // new post
        $new = $this->call('POST', '/api/shouts', ['body' => 'Here be a shout, #1!']);
        $new = $this->call('POST', '/api/shouts', ['body' => 'Here be a shout, #2!']);
        $new = $this->call('POST', '/api/shouts', ['body' => 'Here be a shout, #3!']);

        $fetch = $this->toJson($this->call('GET', '/api/posts', ['after' => $topCursor]));
        $this->assertEquals(count($fetch->posts), 3);
        $this->assertResponseOk();
        $this->assertEquals($fetch->posts[0]->body->body, 'Here be a shout, #3!');
        $this->assertEquals($fetch->posts[1]->body->body, 'Here be a shout, #2!');
        $this->assertEquals($fetch->posts[2]->body->body, 'Here be a shout, #1!');

    }

    /**
     * @test
     */
    public function it_can_display_event_posts()
    {
         $genericEvent = [
            'user_id'   => 1,
            'name'      => 'My Awesome Event',
            'body'      => 'Details of my awesome event',
            'html_body' => 'Details of my awesome event',
            'url'       => 'http://www.google.com',
            'location'  => 'My Awesome Location',
            'cell'      => '',
            'timestamp' => '2014-12-25 12:00:00'
        ];

        $this->registerAndLoginAsMario();
        $this->call('POST', '/api/events', $genericEvent);
        $this->assertResponseOk();

        $fetch = $this->toJson($this->call('GET', '/api/posts'))->posts;
        $this->assertResponseOk();
        $this->assertEquals(count($fetch), 1);
        $this->assertEquals($fetch[0]->body->body, 'Details of my awesome event');
        $this->assertEquals($fetch[0]->type, 'event');
    }

    /**
     * @test
     */
    public function it_can_show_posts_from_one_user()
    {
        $luigi = $this->registerAndLoginAsLuigi();

        // this is a noise post; it's not supposed to show up in the later test
        $this->call('POST', '/api/shouts', ['body' => $this->otherGenericShoutBody]);

        $mario = $this->registerAndLoginAsMario();

        // check for no posts
        $fetch = $this->toJson($this->call('GET', '/api/posts', ['user' => $mario->hash]));
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->posts));

        // add post
        $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        $this->assertResponseOk();

        // proper check
        $fetch = $this->toJson($this->call('GET', '/api/posts', ['user' => $mario->hash]));
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->posts));
        $this->assertEquals($this->genericShoutBody, $fetch->posts[0]->body->body);
        $this->assertEquals($mario->hash, $fetch->posts[0]->links->author->hash);
    }

    /**
     * @test
     */
    public function it_can_display_posts_from_a_users_buddies()
    {
        $mario = $this->registerMario();
        $luigi = $this->registerLuigi();
        $yoshi = $this->registerYoshi();

        // query should be possible with no friends
        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->posts));

        // be friends
        $this->asUser($mario->hash);
        $this->callJson("POST", "/api/users/" . $luigi->hash . "/buddy-requests");
        $this->asUser($luigi->hash);
        $request = $this->callJson("GET", "/api/users/{$luigi->hash}/buddy-requests")->buddy_requests[0]->hash;
        $this->callJson("POST", "/api/users/{$luigi->hash}/buddy-requests/$request/accept");

        // query should be possible with no posts
        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->posts));

        // luigi post
        $this->asUser($luigi->hash);
        $this->call('POST', '/api/shouts', ['body' => 'Luigi is making a shout.']);

        // yoshi post
        $this->asUser($yoshi->hash);
        $this->call('POST', '/api/shouts', ['body' => 'Yoshi is making a shout.']);

        // actual fetch
        $this->asUser($mario->hash);
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->posts));
        $this->assertEquals('Luigi is making a shout.', $fetch->posts[0]->body->body);

        // it should also work with just the query string key with no value
        $fetch = $this->callJson('GET', '/api/posts?buddies');
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->posts));
        $this->assertEquals('Luigi is making a shout.', $fetch->posts[0]->body->body);

        // make sure others can't check this info
        $this->asUser($luigi->hash);
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseStatus(403);
        $this->asUser($yoshi->hash);
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseStatus(403);
        $this->asGuest();
        $fetch = $this->callJson('GET', '/api/posts', ['buddies' => $mario->hash]);
        $this->assertResponseStatus(401);

    }

    /**
     * @test
     */
    public function it_can_use_before_and_after_parameters_on_user_feeds()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->callJson('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        $this->callJson('POST', '/api/shouts', ['body' => $this->otherGenericShoutBody]);

        list($second, $first) = $this->callJson('GET', '/api/posts')->posts;

        $fetchBefore = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'before' => $second->hash]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetchBefore->posts));
        $this->assertEquals($this->genericShoutBody, $fetchBefore->posts[0]->body->body);

        $fetchAfter = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'after' => $first->hash]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetchAfter->posts));
        $this->assertEquals($this->otherGenericShoutBody, $fetchAfter->posts[0]->body->body);
    }

    /**
     * @test
     */
    public function it_can_use_the_take_parameter_to_determine_how_many_posts_to_fetch()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->callJson('POST', '/api/shouts', ['body' => "I am post number 1."]);
        $this->callJson('POST', '/api/shouts', ['body' => "I am post number 2."]);
        $this->callJson('POST', '/api/shouts', ['body' => "I am post number 3."]);
        $this->callJson('POST', '/api/shouts', ['body' => "I am post number 4."]);

        list($fourth, $third, $second, $first) = $this->callJson('GET', '/api/posts')->posts;

        $fetch = $this->callJson('GET', '/api/posts', ['take' => 2]);
        $this->assertResponseOk();
        $this->assertEquals(2, count($fetch->posts));
        $this->assertEquals("I am post number 4.", $fetch->posts[0]->body->body);
        $this->assertEquals("I am post number 3.", $fetch->posts[1]->body->body);

        // check that it can be combined with the other params

        $fetchBefore = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'before' => $third->hash, 'take' => 1]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetchBefore->posts));
        $this->assertEquals("I am post number 2.", $fetchBefore->posts[0]->body->body);

        $fetchAfter = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'after' => $first->hash, 'take' => 1]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetchAfter->posts));
        $this->assertEquals("I am post number 4.", $fetchAfter->posts[0]->body->body);

        // try in-between

        $fetchBetween = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'after' => $first->hash, 'before' => $fourth->hash]);
        $this->assertResponseOk();
        $this->assertEquals(2, count($fetchBetween->posts));
        $this->assertEquals("I am post number 3.", $fetchBetween->posts[0]->body->body);
        $this->assertEquals("I am post number 2.", $fetchBetween->posts[1]->body->body);

        $fetchBetween = $this->callJson('GET', '/api/posts', ['user' => $mario->hash, 'after' => $first->hash, 'before' => $fourth->hash, 'take' => 1]);
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetchBetween->posts));
        $this->assertEquals("I am post number 3.", $fetchBetween->posts[0]->body->body);
    }

    /**
     * @test
     */
    public function a_request_cannot_fetch_more_than_20_posts_with_take()
    {
        $mario = $this->registerAndLoginAsMario();
        for ($i = 0; $i < 50; $i++) {
            $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);
        }

        $fetch = $this->callJson('GET', '/api/posts', ['take' => 40]);
        $this->assertResponseOk();
        $this->assertEquals(20, count($fetch->posts));
    }

    /**
     * @test
     */
    public function posts_define_the_type_of_object_they_wrap()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->call('POST', '/api/shouts', ['body' => $this->genericShoutBody]);

        $fetch = $this->callJson('GET', '/api/posts');
        $this->assertResponseOk();
        $this->assertEquals('shout', $fetch->posts[0]->type);

    }

} 