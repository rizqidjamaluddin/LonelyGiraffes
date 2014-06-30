<?php  namespace Giraffe\Geolocation\Providers;

use DB;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationProvider;
use Illuminate\Support\Collection;

class GeonameLocationProvider implements LocationProvider
{
    const CITY_TABLE = 'lookup_geoname_places';
    const STATE_SEARCH_CAP = 10;
    const CITY_SEARCH_CAP = 5;
    const STATES_TABLE = 'lookup_geoname_states';

    /**
     * @param $hint
     * @return \Giraffe\Geolocation\Location[]
     */
    public function search($hint)
    {
        // look up by city
        $cities = $this->searchForCities($hint);

        // Look up by state - return highest pop city.
        $stateCities = $this->searchViaState($hint);

        // merge and transform
        $cities = $cities->merge($stateCities);
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
     * There isn't a single consistent way to make this a join so only one result is returned each
     * so we'll just do a naive multi-query lookup. Caching will help.
     *
     * @param $hint
     * @return Collection
     */
    protected function searchViaState($hint)
    {
        /** @var Array $states */
        $states = DB::table('lookup_geoname_states')->where('name', 'LIKE', $hint . '%')
                    ->take(self::STATE_SEARCH_CAP)
                    ->get();
        $stateCities = new Collection();
        foreach ($states as $state) {
            $stateCities->push(
                DB::table(self::CITY_TABLE)->where('state_code', $state->state_code)
                  ->where('country_code', $state->country_code)
                  ->orderBy('population', 'desc')
                  ->first()
            );
        }

        return $stateCities;
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
              ->get()
        );

        return $cities;
    }

    /**
     * @param $cities
     * @return array
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

        $results = $results->sortBy(
            function ($location) {
                return $location->population;
            },
            SORT_NUMERIC,
            true
        );

        return $results->toArray();
    }

    /**
     * @param string $city
     * @param string $state
     * @param string $country
     * @return Location
     */
    public function findExact($city, $state, $country)
    {
        $result = DB::table(self::CITY_TABLE)
                    ->where('city', $city)
                    ->where('state', $state)
                    ->where('country', $country)->first();

        if (!$result) throw new NotFoundModelException;

        $place = Location::makeFromCity($city, $state, $country);
        $place->provideCoordinates($result->lat, $result->long);
        return $place;
    }
}