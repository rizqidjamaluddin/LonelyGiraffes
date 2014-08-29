<?php  namespace Giraffe\Geolocation; 

interface ExactLocationProvider
{
    /**
     * @param string $city
     * @param string $state
     * @param string $country
     * @return Location
     */
    public function findExact($city, $state, $country);
} 