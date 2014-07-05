<?php

abstract class GeolocationCase extends AcceptanceCase
{

    public function setUp()
    {
        parent::setUp();
        Artisan::call('lgdb:geonames', ['source' => 'app/data/geonames-1M-testdata.txt']);
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
        ]
    ];

} 