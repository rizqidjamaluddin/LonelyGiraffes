<?php

use Giraffe\Common\Controller;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\LocationTransformer;

class LocationController extends Controller
{
    /**
     * @var Giraffe\Geolocation\LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        parent::__construct();
        $this->locationService = $locationService;
    }


    public function search()
    {
        $results = $this->locationService->search(Input::get('hint'));
        return $this->withCollection($results, new LocationTransformer, 'locations');
    }
} 