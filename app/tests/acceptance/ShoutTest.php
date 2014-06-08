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
        $user = $this->createMemberAccount();
        $this->be($user);

        $response = $this->call('POST', 'api/shouts', [
                'body' => $text
            ]);

        $data = json_decode($response->getContent());

        $this->assertResponseOk();
        $validator = new Validator(app_path() . '/schemas/PostSchema.json');
        $validator->validate($data);

        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $data->post->postable;

        $shoutValidator = new Validator(app_path() . '/schemas/ShoutSchema.json');
        $shoutValidator->validate($shoutChild);


    }
} 