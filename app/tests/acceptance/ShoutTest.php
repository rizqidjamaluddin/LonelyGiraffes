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

        $this->assertResponseOk();
        $validator = new Validator(app_path() . '/schemas/PostSchema.json');
        $validator->validate(json_decode($response->getContent()));

        echo $response->getContent();
    }
} 