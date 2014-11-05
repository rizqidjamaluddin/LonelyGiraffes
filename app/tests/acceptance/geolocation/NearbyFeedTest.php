<?php 
class NearbyFeedTest extends GeolocationCase
{
    /**
     * @test
     */
    public function users_can_see_posts_from_nearby_users_but_not_themselves()
    {
        $mario = $this->registerNYCMario();
        $luigi = $this->registerManhattanLuigi();
        $yoshi = $this->registerLondonYoshi();

        $this->asUser($mario->hash);
        $this->call('POST', '/api/shouts', ['body' => '1 post as Mario']);
        $this->call('POST', '/api/shouts', ['body' => '2 post as Mario']);
        $this->call('POST', '/api/shouts', ['body' => '3 post as Mario']);

        $this->asUser($luigi->hash);
        $this->call('POST', '/api/shouts', ['body' => '1 post as Luigi']);
        $this->call('POST', '/api/shouts', ['body' => '2 post as Luigi']);
        $this->call('POST', '/api/shouts', ['body' => '3 post as Luigi']);

        $this->asUser($yoshi->hash);
        $this->call('POST', '/api/shouts', ['body' => '1 post as Yoshi']);
        $this->call('POST', '/api/shouts', ['body' => '2 post as Yoshi']);
        $this->call('POST', '/api/shouts', ['body' => '3 post as Yoshi']);

        // fetch
        $this->asUser($mario->hash);
        $posts = $this->callJson('GET', '/api/posts?nearby');
        $this->assertResponseOk();
        $this->assertEquals(3, count($posts->posts));
        $this->assertEquals('3 post as Luigi', $posts->posts[0]->body->body);
    }
} 