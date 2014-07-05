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
        'email'    => 'hello@lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Lonely Giraffe',
        'gender'   => 'M'
    ];

    /**
     * @var array
     */
    protected $anotherGenericUser = [
        'email'    => 'anotherHello@lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Lonesome Penguin',
        'gender'   => 'F'
    ];

    /**
     * @var array
     */
    protected $similarGenericUser = [
        'email'    => 'similarHello@lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Lonesome Penguin',
        'gender'   => 'M'
    ];


    public function setUp()
    {
        parent::setUp();
        DB::disableQueryLog();
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

    public $mario = [
        'email'    => 'mario@test.lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Mario',
        'gender'   => 'M'
    ];

    public $luigi = [
        'email'    => 'luigi@test.lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Luigi',
        'gender'   => 'M'
    ];

    public $yoshi = [
        'email'    => 'yoshi@test.lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Yoshi',
        'gender'   => 'X'
    ];

    public $peach = [
        'email'    => 'peach@test.lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Peach',
        'gender'   => 'F'
    ];

    public $bowser = [
        'email'    => 'bowser@evil.test.lonelygiraffes.com',
        'password' => 'password',
        'name'     => 'Bowser',
        'gender'   => 'M'
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