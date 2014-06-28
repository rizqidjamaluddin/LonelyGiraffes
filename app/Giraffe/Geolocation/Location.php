<?php  namespace Giraffe\Geolocation; 

use Illuminate\Support\Contracts\JsonableInterface;

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
     * @var Array
     */
    protected $metadata;

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
        $instance->metadata = $metadata;
        return $instance;
    }

    public function provideCoordinates($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        return $this;
    }

    public function providePopulation($population)
    {
        $this->population = $population;
    }

    public function getCoordinates()
    {
        if (isset($this->lat) && isset($this->long)) {
            return [$this->lat, $this->long];
        }
        return [null, null];
    }

}