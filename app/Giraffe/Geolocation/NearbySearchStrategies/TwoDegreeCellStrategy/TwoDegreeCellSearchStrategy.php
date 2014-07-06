<?php  namespace Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy;

use Giraffe\Common\Repository;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\NearbySearchStrategy;

class TwoDegreeCellSearchStrategy implements NearbySearchStrategy
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