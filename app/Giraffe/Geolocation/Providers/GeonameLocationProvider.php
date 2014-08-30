<?php  namespace Giraffe\Geolocation\Providers;

use DB;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Geolocation\ExactLocationProvider;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationProvider;
use Giraffe\Geolocation\NotFoundLocationException;
use Illuminate\Support\Collection;

class GeonameLocationProvider implements LocationProvider, ExactLocationProvider
{
    const CITY_TABLE = 'lookup_geoname_places';
    const STATE_SEARCH_CAP = 10;
    const CITY_SEARCH_CAP = 5;
    const STATES_TABLE = 'lookup_geoname_states';

    /**
     * @param $hint
     * @return Collection
     */
    public function search($hint)
    {
        // look up by city
        $cities = $this->searchForCities($hint);

        // merge and transform
        $results = $this->transformToLocations($cities);

        return $results;

    }

    /**
     * @param $city
     * @return string
     */
    protected function getCompositeIdentifier($city)
    {
        return $city->country_code . '.' . $city->state_code . '.' . $city->city;
    }

    /**
     * @param $hint
     * @return Collection
     */
    protected function searchForCities($hint)
    {
        // soften the limit for long searches just in case of too many results
        $limit = strlen($hint > 4) ? 20 : self::CITY_SEARCH_CAP;

        $cities = new Collection(
            DB::table(self::CITY_TABLE)
              ->where('city', 'LIKE', $hint . '%')
              ->take($limit)
              ->orderBy('population', 'desc')
              ->rememberForever()
              ->get()
        );

        return $cities;
    }

    /**
     * @param $cities
     * @return Collection
     */
    protected function transformToLocations($cities)
    {
        // registry contains a list of cities in the result set to prevent duplicates
        $registry = [];
        $results = new Collection();
        foreach ($cities as $city) {

            // skip duplicates
            if (in_array($this->getCompositeIdentifier($city), $registry)) {
                continue;
            }

            // convert and fill it data
            $place = Location::makeFromCity($city->city, $city->state, $city->country);
            $place->provideCoordinates($city->lat, $city->long);
            $place->providePopulation($city->population);
            $results->push($place);

            // register identifier for duplicate checks
            $registry[] = $this->getCompositeIdentifier($city);
        }

        return $results;
    }

    /**
     * @param string $city
     * @param string $state
     * @param string $country
     * @throws \Giraffe\Geolocation\NotFoundLocationException
     * @return Location
     */
    public function findExact($city, $state, $country)
    {
        $result = DB::table(self::CITY_TABLE)
                    ->where('city', $city)
                    ->where('state', $state)
                    ->where('country', $country)
                    ->rememberForever()
                    ->first();

        if (!$result) {
            throw new NotFoundLocationException;
        }

        $place = Location::makeFromCity($city, $state, $country);
        $place->provideCoordinates($result->lat, $result->long);
        $place->providePopulation($result->population);
        return $place;
    }

    public function findByStateAndCountryCode($city, $state, $country)
    {
        $result = DB::table(self::CITY_TABLE)
                    ->where('city', $city)
                    ->where('state_code', $state)
                    ->where('country_code', $country)
                    ->rememberForever()
                    ->first();

        if (!$result) {
            throw new NotFoundLocationException;
        }

        $place = Location::makeFromCity($city, $state, $country);
        $place->provideCoordinates($result->lat, $result->long);
        $place->providePopulation($result->population);
        return $place;
    }
}