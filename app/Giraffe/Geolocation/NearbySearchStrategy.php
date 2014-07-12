<?php  namespace Giraffe\Geolocation; 

use Giraffe\Common\Repository;

interface NearbySearchStrategy
{

    /**
     * @param Location   $location
     * @param Repository $repository
     * @param array      $options
     * @return array
     */
    public function searchRepository(Location $location, Repository $repository, $options = []);

    /**
     * Search strategies are allowed to set cache metadata for better performance. The strategy should return this
     * in an string form, to be attached with any models that wish to use store this cache. Later on, this cache
     * should be provided in the $metadata argument of searchRepository under the 'cache' key.
     *
     * @param Location $location
     * @param array    $metadata
     * @return string
     */
    public function getCacheMetadata(Location $location, $metadata =[]);
}