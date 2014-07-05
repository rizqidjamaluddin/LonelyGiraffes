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

    }
}