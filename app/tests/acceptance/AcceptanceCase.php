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
} 