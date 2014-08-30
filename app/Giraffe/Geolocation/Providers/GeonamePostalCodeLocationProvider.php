<?php  namespace Giraffe\Geolocation\Providers; 

use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationProvider;
use Giraffe\Geolocation\NotFoundLocationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class GeonamePostalCodeLocationProvider implements LocationProvider
{

    /**
     * @var GeonameLocationProvider
     */
    private $geonameLocationProvider;

    public function __construct(GeonameLocationProvider $geonameLocationProvider)
    {
        $this->geonameLocationProvider = $geonameLocationProvider;
    }

    /**
     * @param $hint
     * @return Location[]
     */
    public function search($hint)
    {
        try {
            $points = new Collection(\DB::table('lookup_geoname_postal_codes')->where('code', 'LIKE', $hint . '%')->limit(10)->get());
        } catch (QueryException $e) {
            return new Collection;
        }

        $results = new Collection;
        foreach ($points as $point) {
            try {
            $location = $this->geonameLocationProvider->findByStateAndCountryCode($point->city, $point->state_code, $point->country_code);
            } catch (NotFoundLocationException $e) {
                continue;
            }
            $results->push($location);
        }

        return $results;
    }
}