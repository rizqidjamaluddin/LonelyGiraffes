<?php

use Giraffe\Authorization\ProtectedResource;
use Giraffe\Users\UserModel;

class GatekeeperTest extends TestCase
{
    const TEST = 'Giraffe\Authorization\Gatekeeper';
    const PROVIDER = 'Giraffe\Authorization\GatekeeperProvider';
    const GATEKEEPER_EXCEPTION = 'Giraffe\Authorization\GatekeeperException';
    protected $log;

    public function setUp()
    {
        $this->refreshApplication();
        $this->log = App::make("Giraffe\Logging\Log");
    }

    /**
     * @test
     */
    public function it_can_recognize_users()
    {
        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

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
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('edit', 'message')->canI());
        $this->assertTrue($gatekeeper->iAm(1)->andMayI('edit', 'message')->canI());
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
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

        $gatekeeper->iAm(1);
        $this->assertTrue($gatekeeper->mayI('edit', 'message')->canI());
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);
        $gatekeeper->iAm(1);
        $this->assertTrue($gatekeeper->mayI('edit', 'message')->canI());
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);
        $gatekeeper->iAm(1);
        $this->assertFalse($gatekeeper->mayI('delete', 'message')->canI());
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);
        $gatekeeper->iAm(1);
        $this->assertFalse($gatekeeper->mayI('delete', 'user')->canI());
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);
        $gatekeeper->iAm(1);
        $this->assertTrue($gatekeeper->mayI('create', 'user')->canI());

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
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);


        $gatekeeper->disarm();

        $this->assertTrue($gatekeeper->mayI('create', 'message')->canI());
        $this->assertTrue($gatekeeper->mayI('update', 'message')->canI());
        $this->assertTrue($gatekeeper->mayI('delete', 'user')->canI());

        $gatekeeper->iAm('1')->disarm();

        $this->assertTrue($gatekeeper->mayI('create', 'message')->canI());
        $this->assertTrue($gatekeeper->mayI('update', 'message')->canI());
        $this->assertTrue($gatekeeper->mayI('delete', 'user')->canI());
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
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

        $this->assertFalse($gatekeeper->iAm(1)->mayI('delete', 'user')->canI());
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
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'users')->canI());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user')->canI());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user_notes')->canI());
        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user_note')->canI());
    }

    /**
     * You can pass gatekeeper a model using ->forThis($model).
     *
     * @test
     */
    public function it_can_accept_context_for_nouns()
    {
        $this->refreshApplication();

        $user_to_delete = new TestResource('test', 1);
        $user_we_cant_delete = new TestResource('test', 2);

        $provider = Mockery::mock(self::PROVIDER);
        $provider->shouldReceive('getUserModel')->with(1)->andReturn(json_decode('{"id": 1}'));
        $provider->shouldReceive('checkIfUserMay')
                 ->with(Mockery::any(), 'delete', 'user', $user_to_delete)
                 ->andReturn(true);
        $provider->shouldReceive('checkIfUserMay')
                 ->with(Mockery::any(), 'delete', 'user', $user_we_cant_delete)
                 ->andReturn(false);
        App::instance(self::PROVIDER, $provider);
        $gatekeeper = new \Giraffe\Authorization\Gatekeeper($provider, $this->log);

        $this->assertTrue($gatekeeper->iAm(1)->mayI('delete', 'user')->forThis($user_to_delete)->canI());
        $this->assertFalse($gatekeeper->iAm(1)->mayI('delete', 'user')->forThis($user_we_cant_delete)->canI());
    }
}

class TestResource implements ProtectedResource{

    private $resource;
    private $owner;

    public function __construct($resource, $owner)
    {
        $this->resource = $resource;
        $this->owner = $owner;
    }

    public function getResourceName()
    {
        return $this->resource;
    }

    public function checkOwnership(UserModel $user)
    {
        return $this->owner->id == $user->id;
    }
}