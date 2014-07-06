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
        $this->assertEquals('2DC 1,1', $s->getCacheMetadata($location));

        $location->provideCoordinates(-1,1);
        $this->assertEquals('2DC -1,1', $s->getCacheMetadata($location));
        $location->provideCoordinates(1,-1);
        $this->assertEquals('2DC 1,-1', $s->getCacheMetadata($location));
        $location->provideCoordinates(-1,-1);
        $this->assertEquals('2DC -1,-1', $s->getCacheMetadata($location));

        // these coordinates should all be in the same cell, latitude between 89-91 (cell 90)
        $location->provideCoordinates(-89,1);
        $this->assertEquals('2DC -45,1', $s->getCacheMetadata($location));
        $location->provideCoordinates(-89.8,1);
        $this->assertEquals('2DC -45,1', $s->getCacheMetadata($location));
        $location->provideCoordinates(-89.2,1);
        $this->assertEquals('2DC -45,1', $s->getCacheMetadata($location));
        $location->provideCoordinates(-90.7,1);
        $this->assertEquals('2DC -45,1', $s->getCacheMetadata($location));
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