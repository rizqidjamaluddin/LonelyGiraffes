<?php  namespace Giraffe\Helpers\Geolocation;

use stdClass;

interface GeolocationProviderInterface
{

    /**
     * @param string $country
     * @param string $state
     * @param string $city
     *
     * @return Location
     */
    public function getPlace($country, $state ='', $city ='');

    /**
     * Return array of probable places where the user might be, given an arbitrary hint (country, city, state).
     * Valid countries (with no specified states/cities) and state (with no specified city) should be returned
     * as well.
     *
     * In the second example, Houston is returned because it's a highly populated city, and we should sort by
     * population when there's no better metric.
     *
     * How many guesses are returned is up to the provider.
     *
     * 'United States' => {country: 'United States'}
     * 'Texas' => {country: 'United States', state: 'Texas'}, {country: 'United States', state: 'Texas', city: 'Houston'}
     * 'Austin' => {country: 'United States', state: 'Texas', city: 'Austin'}
     * 'Aus' => {country: 'United States', state: 'Texas', city: 'Austin'}
     *
     * @param string $hint
     *
     * @return Location[]
     */
    public function guessPlace($hint);

    /**
     * Get a short (arbitrarily-long) list of locations to suggest to a user if their city isn't listed.
     *
     * @param $country
     * @param $state
     * @param $city
     *
     * @return array
     */
    public function getSuggestedNearbyPlaces($country, $state = '', $city = '');
} 