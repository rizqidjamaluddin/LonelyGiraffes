<?php  namespace Giraffe\Geolocation; 

use Illuminate\Support\Contracts\JsonableInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Location
{

    /**
     * Indicates if a location has been synced with the canonical data provider.
     * @var bool
     */
    protected $canonized = false;

    public $country;
    public $state;
    public $city;

    protected $lat;
    protected $long;

    public $population;

    /**
     * @var string
     */
    public $cacheMetadata = '';

    /**
     * @var LocationProvider
     */
    protected $canonicalSource;

    public function __construct()
    {
        $this->canonicalSource = \App::make('Giraffe\Geolocation\LocationService')->getCanonicalProvider();
    }

    public static function makeFromCity($city, $state, $country, $metadata = [])
    {
        $instance = new static;
        $instance->city = $city;
        $instance->state = $state;
        $instance->country = $country;
        $instance->cacheMetadata = $metadata;
        return $instance;
    }

    public static function buildFromString($location)
    {
        // catch for "City, State, Country" format
        $fragments = explode(',', $location);
        if (count($fragments) == 3) {
            array_walk($fragments, function(&$v){ $v = trim($v); });
            /** @var ExactLocationProvider $canonicalSource */
            $canonicalSource = \App::make('Giraffe\Geolocation\LocationService')->getCanonicalProvider();
            return $canonicalSource->findExact($fragments[0], $fragments[1], $fragments[2]);
        }

        throw new NotFoundHttpException;
    }

    public static function buildFromCity($city, $state, $country)
    {
        /** @var LocationProvider $canonicalSource */
        $canonicalSource = \App::make('Giraffe\Geolocation\LocationService')->getCanonicalProvider();
        return $canonicalSource->findExact($city, $state, $country);
    }

    public function provideCoordinates($lat, $long)
    {
        $this->lat = (float) $lat;
        $this->long = (float) $long;
        return $this;
    }

    public function providePopulation($population)
    {
        $this->population = $population;
    }

    public function provideCity($city, $state, $country)
    {
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        return $this;
    }

    public function provideCacheMetadata($data)
    {
        $this->cacheMetadata = $data;
        return $this;
    }

    public function getCoordinates()
    {
        if (isset($this->lat) && isset($this->long)) {
            return [$this->lat, $this->long];
        }
        return [null, null];
    }

    public function getHumanizedForm()
    {
        $humanized = '';
        if ($this->city) $humanized .= $this->city . ', ';
        if ($this->state) $humanized .= $this->state . ', ';
        if ($this->country) $humanized .= $this->country;
        return $humanized;
    }

}