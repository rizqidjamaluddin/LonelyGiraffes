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

    /**
     * @test
     */
    public function a_user_cannot_enter_an_invalid_location()
    {
        $mario = $this->registerNYCMario();
        $this->assertResponseOk();

        $this->call(
            'PUT',
            '/api/users/' . $mario->hash,
            [
                'city'    => 'Middle',
                'state'   => 'Of',
                'country' => 'Nowhere'
            ]
        );
        $this->assertResponseStatus(422);

        $check = $this->toJson($this->call('GET', '/api/users/' . $mario->hash))->users[0];
        $this->assertResponseOk();
        $this->assertEquals($check->city, $this->cities['nyc']['city']);
        $this->assertEquals($check->state, $this->cities['nyc']['state']);
        $this->assertEquals($check->country, $this->cities['nyc']['country']);
    }

    public function a_user_can_change_their_location()
    {

    }

    /**
     * @test
     */
    public function a_user_cannot_change_another_users_location()
    {
        $mario = $this->registerNYCMario();
        $this->assertResponseOk();

        $bowser = $this->registerAndLoginAsBowser();
        $this->call('PUT', '/api/users/' . $mario->hash, $this->cities['manhattan']);

        $check = $this->toJson($this->call('GET', '/api/users/' . $mario->hash))->users[0];
        $this->assertEquals($check->city, $this->cities['nyc']['city']);
        $this->assertEquals($check->state, $this->cities['nyc']['state']);
        $this->assertEquals($check->country, $this->cities['nyc']['country']);
    }

    /**
     * @test
     */
    public function a_user_can_find_people_near_them()
    {
        $mario = $this->registerNYCMario();
        $luigi = $this->registerManhattanLuigi();
        $yoshi = $this->registerSeattleYoshi();
        // register peach with no location
        $peach = $this->registerPeach();

        $this->asUser($mario->hash);
        $results = $this->toJson($this->call('GET', '/api/users?nearby'));
        $this->assertResponseOk();

        // luigi should be in range, but not yoshi nor peach
        $this->assertEquals(count($results->users), 1);
        $this->assertEquals($results->users[0]->name, $luigi->name);

        // peach should get a 428 Precondition Required because she hasn't declared a location
        $this->asUser($peach->hash);
        $results = $this->toJson($this->call('GET', '/api/users?nearby'));
        $this->assertResponseStatus(428);

    }

    protected function registerNYCMario()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->call('PUT', '/api/users/' . $mario->hash, $this->cities['nyc']);
        return $mario;
    }

    protected function registerManhattanLuigi()
    {
        $luigi = $this->registerAndLoginAsLuigi();
        $this->call('PUT', '/api/users/' . $luigi->hash, $this->cities['manhattan']);
        return $luigi;
    }

    protected function registerSeattleYoshi()
    {
        $yoshi = $this->registerAndLoginAsYoshi();
        $this->call('PUT', '/api/users/' . $yoshi->hash, $this->cities['seattle']);
        return $yoshi;
    }
}