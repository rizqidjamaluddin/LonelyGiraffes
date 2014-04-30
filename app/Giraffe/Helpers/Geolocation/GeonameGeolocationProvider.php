<?php  namespace Giraffe\Helpers\Geolocation;

use DB;
use Giraffe\Exceptions\NotFoundLocationException;

/**
 * Class GeonameGeolocationProvider
 *
 * @link http://www.geonames.org/
 * @package Giraffe\Helpers\Geolocation
 */
class GeonameGeolocationProvider implements GeolocationProviderInterface
{

    protected $table = 'lookup_geoname_places';
    protected $stateTable = 'lookup_geoname_states';
    protected $countryTable = 'lookup_countries';

    public function __construct()
    {

    }

    public function getPlace($country, $state = '', $city = '')
    {
        // if only country is given, assume they're in the highest population city
        if (!$state && !$city) {
            $obj = DB::table($this->table)
                ->where('country', $country)
                ->orderBy('population', 'desc')
                ->select('lat', 'long', 'city', 'state', 'country')
                ->first();

            if (!$obj) throw new NotFoundLocationException($this->getSuggestedNearbyPlaces($country, $state, $city));

            return Location::makeAssumed(
                [[$obj->lat, $obj->long], $obj->country, '', ''],
                [[$obj->lat, $obj->long], $obj->country, $obj->state, $obj->city]
            );
        }

        // same procedure for states
        if (!$city) {
            $obj = DB::table($this->table)
                ->where('country', $country)
                ->where('state', $state)
                ->orderBy('population', 'desc')
                ->select('lat', 'long', 'city', 'state', 'country')
                ->first();

            if (!$obj) throw new NotFoundLocationException($this->getSuggestedNearbyPlaces($country, $state, $city));

            return Location::makeAssumed(
                [[$obj->lat, $obj->long], $obj->country, $obj->state, ''],
                [[$obj->lat, $obj->long], $obj->country, $obj->state, $obj->city]
            );
        }

        // default for rest. For countries with no states, this should work fine.
        $obj = DB::table($this->table)
            ->where('country', $country)
            ->where('state', $state)
            ->where('city', city)
            ->orderBy('population', 'desc')
            ->select('lat', 'long', 'city', 'state', 'country')
            ->first();

        if (!$obj) throw new NotFoundLocationException($this->getSuggestedNearbyPlaces($country, $state, $city));

        return Location::make(
            [[$obj->lat, $obj->long], $obj->country, $obj->state, '']
        );
    }

    public function guessPlace($hint)
    {
        // look for matching states...
        $stateSearch = DB::table($this->stateTable)
            ->where('name', 'LIKE', $hint . '%')
            ->orderBy('population', 'desc')
            ->take(5)
            ->get();

        // ... and look for matching cities.
        $citySearch = DB::table($this->table)
            ->where('city', 'LIKE', $hint . '%')
            ->take(3)
            ->orderBy('population', 'desc')
            ->get();

        $suggestions = [];
        foreach ($stateSearch as $state) {
            $suggestions[] = $this->getPlace($state->country, $state->name);
        }

        foreach ($citySearch as $city) {
            $suggestions[] = Location::make([$city->lat, $city->long], $city->country, $city->state, $city->city);
        }

        return $suggestions;
    }

    public function getSuggestedNearbyPlaces($country, $state = '', $city = '')
    {
        if ($state) {
            $results = DB::table($this->table)
                ->where('country', $country)
                ->where('state', $state)
                ->orderBy('population', 'desc')
                ->select('lat', 'long', 'city', 'state', 'country')
                ->take(5)
                ->get();
        } else {
            $results = DB::table($this->table)
                ->where('country', $country)
                ->orderBy('population', 'desc')
                ->select('lat', 'long', 'city', 'state', 'country')
                ->take(5)
                ->get();
        }

        $return = [];
        foreach ($results as $result) {
            $return[] = Location::make([$result->lat, $result->long], $result->country, $result->state, $result->city);
        }
        return $return;
    }
}