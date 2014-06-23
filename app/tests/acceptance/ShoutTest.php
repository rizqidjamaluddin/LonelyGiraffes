<?php

use Json\Validator;

class ShoutTest extends AcceptanceCase
{
    /**
     * @test
     */
    public function users_can_post_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $shout = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => 'This is a shout!'
            ]
        ));

        $this->assertResponseStatus(200);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_get_specific_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $shout = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => 'This is a shout!'
            ]
        ));

        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $shout->post->postable;
        
        $getShout = $this->toJson($this->call('GET', '/api/shouts/' . $shoutChild->shout->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals('This is a shout!', $getShout->shout->body);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_get_all_of_their_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $shoutBodies = [
            'This is a shout!',
            'This is also a shout!',
            'Another shout I am posting!',
            'Yay for shouts!'
        ];
        $this->asUser($model->users[0]->hash);

        for ($i = 0; $i < count($shoutBodies); $i++) {
            $this->call('POST', '/api/shouts', [
                    'body' => $shoutBodies[$i]
                ]
            );
        }
        
        $getShouts = $this->toJson($this->call('GET', '/api/shouts/user/' . $model->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($shoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($shoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($shoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($shoutBodies[3], $getShouts->shouts[3]->body);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_get_all_of_another_users_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $shoutBodies = [
            'This is a shout!',
            'This is also a shout!',
            'Another shout I am posting!',
            'Yay for shouts!'
        ];
        $moreShoutBodies = [
            'This is a shout from another user!',
            'This is also a shout from another user!',
            'Another shout I am posting from another user!',
            'Yay for shouts from another user!'
        ];

        $this->asUser($model->users[0]->hash);

        for ($i = 0; $i < count($shoutBodies); $i++) {
            $this->call('POST', '/api/shouts', [
                    'body' => $shoutBodies[$i]
                ]
            );
        }

        $this->asUser($anotherModel->users[0]->hash);

        $getShouts = $this->toJson($this->call('GET', '/api/shouts/user/' . $model->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($shoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($shoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($shoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($shoutBodies[3], $getShouts->shouts[3]->body);

        for ($i = 0; $i < count($moreShoutBodies); $i++) {
            $this->call('POST', '/api/shouts', [
                    'body' => $moreShoutBodies[$i]
                ]
            );
        }

        $this->asUser($model->users[0]->hash);

        $getShouts = $this->toJson($this->call('GET', '/api/shouts/user/' . $anotherModel->users[0]->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals($moreShoutBodies[0], $getShouts->shouts[0]->body);
        $this->assertEquals($moreShoutBodies[1], $getShouts->shouts[1]->body);
        $this->assertEquals($moreShoutBodies[2], $getShouts->shouts[2]->body);
        $this->assertEquals($moreShoutBodies[3], $getShouts->shouts[3]->body);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function a_user_cannot_delete_another_users_shout()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $anotherModel = $this->toJson($this->call('POST', '/api/users/', $this->anotherGenericUser));
        $this->asUser($model->users[0]->hash);

        $shout = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => 'This is a shout!'
            ]
        ));
        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $shout->post->postable;

        $this->asUser($anotherModel->users[0]->hash);
        $deleteShout = $this->toJson($this->call('DELETE', '/api/shouts/' . $shoutChild->shout->hash));
        $this->assertResponseStatus(403);
    }

    /**
     * @test
     * @depends users_can_post_shouts
     */
    public function users_can_delete_specific_shouts()
    {
        $model = $this->toJson($this->call('POST', '/api/users/', $this->genericUser));
        $this->asUser($model->users[0]->hash);

        $shout = $this->toJson($this->call('POST', '/api/shouts', [
                'body' => 'This is a shout!'
            ]
        ));

        // we need to do this because the ShoutSchema expects syntax like {"shout": {}}
        $shoutChild = new StdClass();
        $shoutChild->shout = $shout->post->postable;
        
        $deleteShout = $this->toJson($this->call('DELETE', '/api/shouts/' . $shoutChild->shout->hash));
        $this->assertResponseStatus(200);

        $getShout = $this->toJson($this->call('GET', '/api/shouts/' . $shoutChild->shout->hash));
        $this->assertResponseStatus(404);
    }
} 