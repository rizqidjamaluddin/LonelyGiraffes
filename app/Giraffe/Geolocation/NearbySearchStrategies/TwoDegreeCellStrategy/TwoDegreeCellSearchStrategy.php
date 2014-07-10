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

        return $repository->twoDegreeCellSearch($cacheMetadata, $limit, $options);
    }

    /**
     * The 2DC strategy splits the globe into cells 5x5 degrees wide each; on the equator, these are roughly
     * 200x200km large, a crude guess of how close would be considered "close". The actual search uses 9 cells;
     * the main cell where the point of interest lies, and the 8 adjacent cells. This avoids cases where the
     * entity is near a cell edge and the search fails to find another entity just across the line; anything within
     * a minimum distance of 200km is guaranteed to be found.
     *
     * Cells are defined by their center point.
     *
     * The cell cache, in the format of "2DC 10,-20", which refers to the cell LAT +19~21/LONG -39~41 (multiplied by 2
     * from the cache syntax).
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

        $cellLat = round($lat / 2);
        $cellLong = round($long / 2);

        // make sure nobody gets stuck in a 0 cell
        if ($cellLong == 0) {
            $cellLong += 1;
        }
        if ($cellLat == 0) {
            $cellLat += 1;
        }

        return '2DC ' . $cellLat . ',' . $cellLong;
    }
}