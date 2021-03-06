<?php 
class EventParticipationTest extends AcceptanceCase
{

    protected $boilerplateEvent = [
        'name'      => 'My Awesome Event',
        'body'      => 'Details of my awesome event',
        'url'       => 'http://www.google.com',
        'location'  => 'My Event Location',
        'timestamp' => '2016-12-25 00:00:00'
    ];

    /**
     * @test
     */
    public function users_can_say_they_are_going_to_an_event()
    {
        $mario = $this->registerAndLoginAsMario();
        $event = $this->callJson('POST', '/api/events', $this->boilerplateEvent);
        $destination = $event->events[0]->hash;

        // mario can see who's going here; both methods are acceptable
        $participants = $this->callJson('GET', "/api/events/{$destination}/participants");
        $this->assertResponseOk();
        $this->assertEquals(0, count($participants->participants));
        $participants = $this->callJson('GET', "/api/events/{$destination}")->events[0]->participants;
        $this->assertResponseOk();
        $this->assertEquals(0, count($participants));

        $luigi = $this->registerAndLoginAsLuigi();
        $r = $this->callJson('POST', "/api/events/{$destination}/join");
        $this->assertResponseOk();

        $this->assertOnlyLuigiAttending($destination);

    }

    /**
     * @test
     */
    public function a_user_cannot_join_an_event_twice()
    {
        $mario = $this->registerAndLoginAsMario();
        $event = $this->callJson('POST', '/api/events', $this->boilerplateEvent);
        $destination = $event->events[0]->hash;

        $luigi = $this->registerAndLoginAsLuigi();
        $r = $this->callJson('POST', "/api/events/{$destination}/join");
        $this->assertResponseOk();
        $r = $this->callJson('POST', "/api/events/{$destination}/join");
        $this->assertResponseStatus(422);

        $this->assertOnlyLuigiAttending($destination);
    }

    /**
     * @param $destination
     */
    protected function assertOnlyLuigiAttending($destination)
    {
        $participants = $this->callJson('GET', "/api/events/{$destination}/participants");
        $this->assertResponseOk();
        $this->assertEquals(1, count($participants->participants));
        $this->assertEquals($this->luigi['name'], $participants->participants[0]->name);
        $participants = $this->callJson('GET', "/api/events/{$destination}")->events[0]->participants;
        $this->assertResponseOk();
        $this->assertEquals(1, count($participants));
        $this->assertEquals($this->luigi['name'], $participants[0]->name);
    }


} 