<?php

use Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchStrategy;

class TwoDegreeCellSearchStrategyTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_transform_coordinates_to_a_cache_string()
    {
        /** @var TwoDegreeCellSearchStrategy $s */
        $s = App::make('Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchStrategy');

        $location = $this->makeLocation();
        $location->provideCoordinates(1,1);
        $this->assertEquals('2DC 0,0', $s->getCacheMetadata($location));

        $location->provideCoordinates(-1,1);
        $this->assertEquals('2DC -2,0', $s->getCacheMetadata($location));
        $location->provideCoordinates(1,-1);
        $this->assertEquals('2DC 0,-2', $s->getCacheMetadata($location));
        $location->provideCoordinates(-1,-1);
        $this->assertEquals('2DC -2,-2', $s->getCacheMetadata($location));

        // these coordinates should all be in the same cell, latitude between 50-52 degrees south (cell -50)
        $location->provideCoordinates(-50,1);
        $this->assertEquals('2DC -50,0', $s->getCacheMetadata($location));
        $location->provideCoordinates(-50.8,1);
        $this->assertEquals('2DC -50,0', $s->getCacheMetadata($location));
        $location->provideCoordinates(-51.2,1);
        $this->assertEquals('2DC -50,0', $s->getCacheMetadata($location));
        $location->provideCoordinates(-52.0,1);
        $this->assertEquals('2DC -50,0', $s->getCacheMetadata($location));
    }

    /**
     * @return \Giraffe\Geolocation\Location
     */
    protected function makeLocation()
    {
        $location = new \Giraffe\Geolocation\Location();
        return $location;
    }
} 