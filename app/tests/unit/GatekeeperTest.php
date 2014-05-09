<?php

class GatekeeperTest extends TestCase
{

    const TEST = 'Giraffe\Helpers\Rights\Gatekeeper';
    const PROVIDER = 'Giraffe\Helpers\Rights\GatekeeperProvider';
    const GATEKEEPER_EXCEPTION = 'Giraffe\Helpers\Rights\GatekeeperException';

    /**
     * @test
     */
    public function it_can_recognize_users()
    {
        $this->refreshApplication();
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode("{'id': 1}"));
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
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode("{'id': 1}"));
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
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode("{'id': 1}"));
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
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode("{'id': 1}"));
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
}