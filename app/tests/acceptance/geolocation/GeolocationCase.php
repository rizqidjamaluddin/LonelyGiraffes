<?php

abstract class GeolocationCase extends AcceptanceCase
{

    public function setUp()
    {
        parent::setUp();
        Artisan::call('lg:db:geonames', ['source' => '/data/geonames-1M-testdata.txt']);
    }

    // these cities are guaranteed to be close enough to be considered "local" and are in the data set
    protected $cities =[
        'nyc' => [
            'city' => 'New York City',
            'state' => 'New York',
            'country' => 'United States'
        ],
        'manhattan' => [
            'city' => 'Manhattan',
            'state' => 'New York',
            'country' => 'United States'
        ],
        'brooklyn' => [
            'city' => 'Brooklyn',
            'state' => 'New York',
            'country' => 'United States'
        ],
        'london' => [
            'city' => 'London',
            'state' => 'England',
            'country' => 'United Kingdom'
        ]
    ];



    protected function registerNYCMario()
    {
        $mario = $this->registerAndLoginAsMario();
        $this->call('PUT', '/api/users/' . $mario->hash, $this->cities['nyc']);
        $this->asUser($mario->hash);
        return $mario;
    }

    protected function registerManhattanLuigi()
    {
        $luigi = $this->registerAndLoginAsLuigi();
        $this->call('PUT', '/api/users/' . $luigi->hash, $this->cities['manhattan']);
        $this->asUser($luigi->hash);
        return $luigi;
    }

    protected function registerLondonYoshi()
    {
        $yoshi = $this->registerAndLoginAsYoshi();
        $this->call('PUT', '/api/users/' . $yoshi->hash, $this->cities['london']);
        $this->asUser($yoshi->hash);
        return $yoshi;
    }

} 