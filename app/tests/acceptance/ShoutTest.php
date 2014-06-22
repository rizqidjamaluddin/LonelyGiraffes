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
        $this->asUser($model->data->hash);

        $response = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => $text
            ]));

        $this->assertResponseStatus(200);
        $validator = new Validator(app_path() . '/schemas/PostSchema.json');
        $validator->validate($response);

        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $response->post->postable;

        $shoutValidator = new Validator(app_path() . '/schemas/ShoutSchema.json');
        $shoutValidator->validate($shoutChild);


    }
} 