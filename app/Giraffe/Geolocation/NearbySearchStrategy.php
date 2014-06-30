<?php  namespace Giraffe\Geolocation; 

use Giraffe\Common\Repository;

interface NearbySearchStrategy
{

    /**
     * @param Location   $location
     * @param Repository $repository
     * @param array      $metadata
     * @param int        $limit
     * @return array
     */
    public function searchRepository(Location $location, Repository $repository, $metadata = [], $limit = 10);
}