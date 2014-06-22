<?php

use Json\Validator;

class ShoutTest extends AcceptanceCase
{
    /**
     * @test
     */
    public function users_can_post_shouts()
    {
        $text = 'This is a shout!';
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $response = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => $text
            ]));

        $this->assertResponseStatus(200);

        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $response->post->postable;


    }
} 