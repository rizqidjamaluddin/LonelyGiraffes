<?php

use Giraffe\Authorization\Gatekeeper;

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
    protected $administrator = [
        'email'     => 'anotherHello@lonelygiraffes.com',
        'password'  => 'password',
        'firstname' => 'Lonely',
        'lastname'  => 'Giraffe',
        'gender'    => 'M'
    ];


    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
    }

    public function toJson($model) 
    {
        return json_decode($model->getContent());
    }
} 