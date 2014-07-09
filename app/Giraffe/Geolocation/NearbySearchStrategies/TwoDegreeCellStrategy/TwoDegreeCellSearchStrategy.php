<?php  namespace Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy;

use Giraffe\Common\ConfigurationException;
use Giraffe\Common\Repository;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\NearbySearchStrategy;

class TwoDegreeCellSearchStrategy implements NearbySearchStrategy
{

    /**
     * @param Location   $location
     * @param Repository $repository
     * @param int        $limit
     * @param array      $options
     * @throws \Giraffe\Common\ConfigurationException
     * @return array
     */
    public function searchRepository(Location $location, Repository $repository, $limit = 10, $options = [])
    {
        $cacheMetadata = $location->cacheMetadata;

        // validate cache metadata
        if (strpos($cacheMetadata, '2DC') === false) {
            // figure out proper cache if it's not available
            list($lat, $long) = $location->getCoordinates();
            if (!$lat || !$long) {
                // if no coordinates are given, make a new location object and re-call this method with it
                $location = Location::buildFromCity($location->city, $location->state, $location->country);
                return $this->searchRepository($location, $repository, $cacheMetadata, $limit);
            }
            $cacheMetadata = $this->getCacheMetadata($location);
        }

        if (!($repository instanceof TwoDegreeCellSearchableRepository)) {
            throw new ConfigurationException(get_class($repository) . ' must implement TwoDegreeCellSearchableRepository');
        }

        // generate cache string for all the adjacent cells as well
        $cacheList = [];


        return $repository->twoDegreeCellSearch($cacheList, $limit, $options);
    }

    /**
     * The 2DC strategy splits the globe into cells 2x2 degrees wide each; on the equator, these are roughly
     * 200x200km large, a crude guess of how close would be considered "close". The actual search uses 9 cells;
     * the main cell where the point of interest lies, and the 8 adjacent cells. This avoids cases where the
     * entity is near a cell edge and the search fails to find another entity just across the line; anything within
     * a minimum distance of 200km is guaranteed to be found.
     *
     * Cells are defined by their southwest point.
     *
     * The cell cache is in the format of "2DC 10,-20", which refers to the cell LAT +10~12/LONG -20~18.
     *
     * @param Location $location
     * @param array    $metadata
     * @return string
     */
    public function getCacheMetadata(Location $location, $metadata = [])
    {
        list($lat, $long) = $location->getCoordinates();

        // return false if there's no available lat/long
        if (!$lat || !$long) {
            return '';
        }

        $cellLat = floor($lat / 2) * 2;
        $cellLong = floor($long / 2) * 2;

        return '2DC ' . $cellLat . ',' . $cellLong;
    }
}