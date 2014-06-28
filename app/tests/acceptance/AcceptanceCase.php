<?php

use Giraffe\Authorization\Gatekeeper;
use Illuminate\Http\Response;

abstract class AcceptanceCase extends TestCase
{

    /**
     * @var Gatekeeper
     */
    protected $gatekeeper;

    /**
     * @var array
     */
    protected $genericUser = [
        'email'     => 'hello@lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Lonely',
        'lastname'  => 'Giraffe',
        'gender'    => 'M'
    ];

    /**
     * @var array
     */
    protected $anotherGenericUser = [
        'email'     => 'anotherHello@lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Lonely',
        'lastname'  => 'Giraffe',
        'gender'    => 'M'
    ];

    /**
     * @var array
     */
    protected $event = [
        'user_id'   => 1,
        'name'      => 'My Awesome Event',
        'body'      => 'Details of my awesome event',
        'html_body' => 'Details of my awesome event',
        'url'       => 'http://www.google.com',
        'location'  => 'My Awesome Location',
        'city'      => 'Athens',
        'state'     => 'Georgia',
        'country'   => 'US',
        'cell'      => '',
        'timestamp' => '0000-00-00 00:00:00'
    ];


    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
    }

    public function toJson(Response $model)
    {
        return json_decode($model->getContent());
    }

    /*
     * -- Fake user data --
     */

    protected $mario = [
        'email'     => 'mario@test.lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Mario',
        'lastname'  => 'N',
        'gender'    => 'M'
    ];

    protected $luigi = [
        'email'     => 'luigi@test.lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Luigi',
        'lastname'  => 'N',
        'gender'    => 'M'
    ];

    protected $yoshi = [
        'email'     => 'yoshi@test.lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Yoshi',
        'lastname'  => 'N',
        'gender'    => 'X'
    ];

    protected $peach = [
        'email'     => 'peach@test.lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Peach',
        'lastname'  => 'N',
        'gender'    => 'F'
    ];

    protected $bowser = [
        'email'     => 'bowser@evil.test.lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Bowser',
        'lastname'  => 'N',
        'gender'    => 'M'
    ];

    public function registerAndLoginAsMario()
    {
        $mario = $this->registerMario();
        $this->asUser($mario->hash);
        return $mario;
    }

    public function registerMario()
    {
        $mario = $this->toJson($this->call("POST", "/api/users/", $this->mario))->users[0];
        return $mario;
    }

    public function registerAndLoginAsLuigi()
    {
        $luigi = $this->registerLuigi();
        $this->asUser($luigi->hash);
        return $luigi;
    }

    public function registerLuigi()
    {
        $luigi = $this->toJson($this->call("POST", "/api/users/", $this->luigi))->users[0];
        return $luigi;
    }

    public function registerAndLoginAsYoshi()
    {
        $yoshi = $this->registerYoshi();
        $this->asUser($yoshi->hash);
        return $yoshi;
    }

    public function registerYoshi()
    {
        $yoshi = $this->toJson($this->call("POST", "/api/users/", $this->yoshi))->users[0];
        return $yoshi;
    }

    public function registerAndLoginAsPeach()
    {
        $peach = $this->registerPeach();
        $this->asUser($peach->hash);
        return $peach;
    }

    public function registerPeach()
    {
        $peach = $this->toJson($this->call("POST", "/api/users/", $this->peach))->users[0];
        Artisan::call('lgutil:promote', ['email' => $this->peach['email'], '--force' => true]);
        return $peach;
    }

    public function registerAndLoginAsBowser()
    {
        $bowser = $this->registerBowser();
        $this->asUser($bowser->hash);
        return $bowser;
    }

    public function registerBowser()
    {
        $bowser = $this->toJson($this->call("POST", "/api/users/", $this->bowser))->users[0];
        return $bowser;
    }
} 