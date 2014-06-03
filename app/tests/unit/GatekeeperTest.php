<?php

class GatekeeperTest extends TestCase
{
    const TEST = 'Giraffe\Authorization\Gatekeeper';
    const PROVIDER = 'Giraffe\Authorization\GatekeeperProvider';
    const GATEKEEPER_EXCEPTION = 'Giraffe\Authorization\GatekeeperException';

    /**
     * @test
     */
    public function it_can_recognize_users()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $gatekeeper->iAm('1');
        $this->assertEquals($gatekeeper->fetchMyModel()->id, 1);
    }

    /**
     * @test
     */
    public function it_should_allow_a_user_to_do_a_verb_on_an_entity()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'edit', 'message')->andReturn(true);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('edit', 'message')->please());
        $this->assertTrue($gatekeeper->iAm(1)->andMayI('edit', 'message')->please());
    }

    /**
     * @test
     */
    public function it_should_be_a_singleton()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $gatekeeper->iAm('1');

        $second_gatekeeper = App::make(self::TEST);
        $this->assertEquals($second_gatekeeper->fetchMyModel()->id, 1);
    }

    /**
     * @test
     */
    public function it_should_reset_after_each_question()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'edit', 'message')->andReturn(true);
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'delete', 'message')->andReturn(false);
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'delete', 'user')->andReturn(false);
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'create', 'user')->andReturn(true);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $gatekeeper->iAm(1);
        $this->assertTrue($gatekeeper->mayI('edit', 'message')->please());
        $gatekeeper = App::make(self::TEST);
        $this->assertTrue($gatekeeper->mayI('edit', 'message')->please());
        $gatekeeper = App::make(self::TEST);
        $this->assertFalse($gatekeeper->mayI('delete', 'message')->please());
        $gatekeeper = App::make(self::TEST);
        $this->assertFalse($gatekeeper->mayI('delete', 'user')->please());
        $gatekeeper = App::make(self::TEST);
        $this->assertTrue($gatekeeper->mayI('create', 'user')->please());

    }

    /**
     * @test
     */
    public function it_can_be_disarmed_for_testing_needs()
    {
        $this->refreshApplication();
        // set up a fake mock to make sure it's not actually being called
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andThrow('Exception');
        $provider->shouldReceive('checkIfUserMay')->withAnyArgs()->andReturn(false);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);


        $gatekeeper->disarm();

        $this->assertTrue($gatekeeper->mayI('create', 'message')->please());
        $this->assertTrue($gatekeeper->mayI('update', 'message')->please());
        $this->assertTrue($gatekeeper->mayI('delete', 'user')->please());

        $gatekeeper->iAm('1')->disarm();

        $this->assertTrue($gatekeeper->mayI('create', 'message')->please());
        $this->assertTrue($gatekeeper->mayI('update', 'message')->please());
        $this->assertTrue($gatekeeper->mayI('delete', 'user')->please());
    }

    /**
     * @test
     */
    public function it_should_not_authenticate_a_null_user()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(null);
        $provider->shouldReceive('checkIfUserMay')->with(null, 'delete', 'user')->andThrow('Exception');
        $provider->shouldReceive('checkIfGuestMay')->with('delete', 'user')->andReturn(false);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $this->assertFalse($gatekeeper->iAm(1)->mayI('delete', 'user')->please());
    }

    /**
     * Gatekeeper should use singular nouns, and convert any plurals into singular format.
     *
     * @test
     */
    public function it_can_accept_plural_and_singular_nouns()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'delete', 'user')->andReturn(true);
        $provider->shouldReceive('checkIfUserMay')->with(Mockery::any(), 'delete', 'user_note')->andReturn(true);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'users')->please());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user')->please());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user_notes')->please());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user_note')->please());
    }

    /**
     * You can pass gatekeeper a model using ->forThis($model).
     *
     * @test
     */
    public function it_can_accept_context_for_nouns()
    {
        $this->refreshApplication();

        $user_to_delete = json_decode('{"id": 10}');
        $user_we_cant_delete = json_decode('{"id": 11}');

        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode("{'id': 1}"));
        $provider->shouldReceive('checkIfUserMay')
                 ->with(Mockery::any(), 'delete', 'user', $user_to_delete)
                 ->andReturn(true);
        $provider->shouldReceive('checkIfUserMay')
                 ->with(Mockery::any(), 'delete', 'user', $user_we_cant_delete)
                 ->andReturn(false);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = App::make(self::TEST);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user')->forThis($user_to_delete)->please());
        $this->assertFalse($gatekeeper->iAm(1)->mayI('delete', 'user')->forThis($user_we_cant_delete)->please());
    }
}