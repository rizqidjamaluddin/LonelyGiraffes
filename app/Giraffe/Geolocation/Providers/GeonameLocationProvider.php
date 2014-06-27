<?php  namespace Giraffe\Geolocation\Providers;

use DB;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationProvider;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class GeonameLocationProvider implements LocationProvider
{
    const CITY_TABLE = 'lookup_geoname_places';
    const STATE_SEARCH_CAP = 10;

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

        // look up by country - return highest pop city
        $countryCities = $this->searchViaCountry($hint);

        // merge and transform
        $cities = $cities->merge($stateCities, $countryCities);
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
    protected function searchViaCountry($hint)
    {
        /** @var Array $countries */
        $countries = DB::table('lookup_countries')->where('name', 'LIKE', '%' . $hint . '%')->get();
        $countryCities = new Collection();
        foreach ($countries as $country) {
            $countryCities->push(
                DB::table(self::CITY_TABLE)
                  ->where('country_code', $country->code)
                  ->orderBy('population', 'desc')
                  ->first()
            );
        }
        $countryCities->sortBy('population', SORT_NUMERIC, true);
        return $countryCities;
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
        $states = DB::table('lookup_geoname_states')->where('name', 'LIKE', '%' . $hint . '%')
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
        $stateCities->sortBy('population', SORT_NUMERIC, true);
        return $stateCities;
    }

    /**
     * @param $hint
     * @return Collection
     */
    protected function searchForCities($hint)
    {
        $cities = new Collection(DB::table(self::CITY_TABLE)->where('city', 'LIKE', '%' . $hint . '%')->get());
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
        $results = [];
        foreach ($cities as $city) {

            // skip duplicates
            if (in_array($this->getCompositeIdentifier($city), $registry)) {
                continue;
            }

            // convert and fill it data
            $place = Location::makeFromCity($city->city, $city->state, $city->country);
            $place->provideCoordinates($city->lat, $city->long);
            $results[] = $place;

            // register identifier for duplicate checks
            $registry[] = $this->getCompositeIdentifier($city);
        }
        return $results;
    }

}