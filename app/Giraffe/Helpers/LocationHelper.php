<?php  namespace Giraffe\Helpers;

use Giraffe\Helpers\Geolocation\GeolocationProviderInterface;

/**
 * Class LocationHelper
 *
 * Assist in the conversion from place names into "cell" names. Cells are LG domain logic concepts in which one cell
 * represents a 5 by 5 degree space. When searching for "nearby" items, we can query for entities which exist within
 * the same or adjacent cells from the searcher. This allows for very fast and efficient searches, without requiring
 * heavy exact calculations for distance.
 *
 * This equates to an approximate 1500 by 1500 kilometer search area (roughly centered upon the searcher) near the equator,
 * and shrinks to 1500 by 750km near 60 degrees latitude (e.g. Alaska).
 *
 * Cells are represented as the string 'X,Y' in which X and Y vary from -36 to 36.
 *
 * @package Giraffe\Helpers
 */
class LocationHelper
{

    /**
     * @var Geolocation\GeolocationProviderInterface
     */
    private $geolocationProvider;

    public function __construct(GeolocationProviderInterface $geolocationProvider)
    {
        $this->geolocationProvider = $geolocationProvider;
    }

    public function convertPlaceToCell($country, $city)
    {

    }

    public function getAdjacentCells($cell)
    {

    }

} 