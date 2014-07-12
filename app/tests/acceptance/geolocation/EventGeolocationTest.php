<?php

class EventGeolocationTest extends GeolocationCase
{

    protected $genericEvent = [
        'name'      => 'Iron Man Pajama Party',
        'body'      => "Come on over and join the world's biggest ever iron man-themed pajama party!",
        'location'  => 'My Penthouse',
        'city'      => 'New York City',
        'state'     => 'New York',
        'country'   => 'United States',
        'timestamp' => '2017-10-10 20:00:00'
    ];

    protected $otherGenericEvent = [
        'name'      => 'Chicken-Costume Get-Together',
        'body'      => "Come on, you know you've got that costume saved for just for this occasion.",
        'location'  => 'My Apartment',
        'city'      => 'Manhattan',
        'state'     => 'New York',
        'country'   => 'United States',
        'timestamp' => '2017-12-20 22:00:00'
    ];

    protected $distantGenericEvent = [
        'name'      => 'Really Far-Away Stuff',
        'body'      => "We cool people live really far away from everyone else.",
        'location'  => 'My Cottage',
        'city'      => 'London',
        'state'     => 'England',
        'country'   => 'United Kingdom',
        'timestamp' => '2017-12-20 23:00:00'
    ];

    /**
     * @test
     */
    public function events_can_have_a_location()
    {
        $this->registerAndLoginAsMario();
        $response = $this->toJson($this->call('POST', '/api/events', $this->genericEvent));
        $this->assertResponseOk();
        $this->assertEquals(count($response->events), 1);
    }

    /**
     * @test
     */
    public function users_can_find_events_near_them_and_be_ordered_by_time_to_event()
    {
        $mario = $this->registerNYCMario();
        $response = $this->toJson($this->call('GET', '/api/events?nearby'));
        $this->assertResponseOk();
        $this->assertEquals(0, count($response->events));

        $luigi = $this->registerManhattanLuigi();
        $response = $this->toJson($this->call('POST', '/api/events', $this->otherGenericEvent));
        $this->assertResponseOk();

        $peach = $this->registerPeach();
        $response = $this->toJson($this->call('POST', '/api/events', $this->genericEvent));
        $this->assertResponseOk();
        $response = $this->toJson($this->call('POST', '/api/events', $this->distantGenericEvent));
        $this->assertResponseOk();


        $this->asUser($mario->hash);
        $response = $this->toJson($this->call('GET', '/api/events?nearby'));
        $this->assertResponseOk();
        $this->assertEquals(2, count($response->events));

    }
} 