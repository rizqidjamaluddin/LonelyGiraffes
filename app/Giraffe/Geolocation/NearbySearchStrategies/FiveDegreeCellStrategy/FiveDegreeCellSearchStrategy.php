<?php  namespace Giraffe\Geolocation\NearbySearchStrategies\FiveDegreeCellStrategy;

use Giraffe\Common\Repository;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\NearbySearchStrategy;

class FiveDegreeCellSearchStrategy implements NearbySearchStrategy
{

    /**
     * @param Location   $location
     * @param Repository $repository
     * @param array      $metadata
     * @param int        $limit
     * @return array
     */
    public function searchRepository(Location $location, Repository $repository, $metadata = [], $limit = 10)
    {
        // TODO: Implement searchRepository() method.
    }
}