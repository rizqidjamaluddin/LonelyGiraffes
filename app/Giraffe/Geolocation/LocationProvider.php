<?php  namespace Giraffe\Geolocation;

interface LocationProvider
{
    /**
     * @param $hint
     * @return Location[]
     */
    public function search($hint);

    /**
     * @param string $city
     * @param string $state
     * @param string $country
     * @return Location
     */
    public function findExact($city, $state, $country);
}