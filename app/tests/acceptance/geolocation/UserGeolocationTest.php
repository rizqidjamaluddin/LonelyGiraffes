<?php

class UserGeolocationTest extends GeolocationCase
{
    /**
     * @test
     */
    public function a_user_can_define_their_location()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->call('PUT', '/api/users/' . $mario->hash, $this->cities['nyc']);
        $this->assertResponseOk();

        $check = $this->toJson($this->call('GET', '/api/users/' . $mario->hash))->users[0];
        $this->assertEquals($check->city, $this->cities['nyc']['city']);
        $this->assertEquals($check->state, $this->cities['nyc']['state']);
        $this->assertEquals($check->country, $this->cities['nyc']['country']);

    }

    public function a_user_cannot_enter_an_invalid_location()
    {

    }

    public function a_user_can_change_their_location()
    {

    }

    public function a_user_cannot_change_another_users_location()
    {
        
    }
}