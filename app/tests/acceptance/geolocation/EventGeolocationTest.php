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

    /**
     * @test
     */
    public function events_can_have_a_location()
    {
        $response = $this->toJson($this->call('POST', '/api/events', []));
        $this->assertResponseOk();
        $this->assertEquals(count($response->events), 1);
    }

    /**
     * @test
     */
    public function users_can_find_events_near_them_and_be_ordered_by_time_to_event()
    {



        $response = $this->toJson($this->call('GET', '/api/events?nearby'));
        $this->assertResponseOk();
        $this->assertEquals(count($response->events), 1);
    }
} 