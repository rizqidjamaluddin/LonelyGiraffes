<?php  namespace Giraffe\Geolocation;

use App;
use Illuminate\Support\Contracts\JsonableInterface;
use JsonSerializable;

/**
 * Class Location
 *
 * A location value object for representing a location.
 *
 * @package Giraffe\Helpers\Geolocation
 */
class Location implements JsonableInterface, JsonSerializable
{

    public $lat;
    public $long;

    public $country;
    public $state;
    public $city;

    public $is_assumed = false;
    public $assumed_location;

    /**
     * @var GeolocationProviderInterface
     */
    protected $provider;

    public function __construct(GeolocationProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param array  $coordinates
     * @param string $country
     * @param string $state
     * @param string $city
     *
     * @return Location
     */
    public static function make($coordinates, $country = '', $state = '', $city = '')
    {
        /** @var Location $location */
        $location = App::make('Giraffe\Helpers\Geolocation\Location');
        return $location->setPlace($coordinates, $country, $state, $city);
    }

    /**
     * Create a new "assumed" location, used for when the user gives an incomplete location. The embedded 'assumed'
     * object lets us inform the user of where we think they are. Arrays are numeric-indexed that mimic make().
     *
     * @param array $given
     * @param array $assumed
     *
     * @return Location
     */
    public static function makeAssumed(array $given, array $assumed)
    {
        $location = self::make($given[0], $given[1], $given [2] ?: '', $given[3] ?: '');
        $location->setAssumedPlace($assumed[0], $assumed[1], $assumed[2] ?: '', $assumed[3] ?: '');
        return $location;
    }

    protected function setPlace($coordinates, $country = '', $state = '', $city ='')
    {
        $this->lat = $coordinates[0];
        $this->long = $coordinates[1];
        $this->country = $country;
        $this->state = $state;
        $this->city = $city;
        return $this;
    }

    public function setAssumedPlace($coordinates, $country, $state = '', $city = '')
    {
        $this->is_assumed = true;
        $this->assumed_location = $this->make($coordinates, $country, $state, $city);
    }

    public function toJson($options = 0)
    {
        $obj = new \stdClass;
        $obj->lat = $this->lat;
        $obj->long = $this->long;
        $obj->country = $this->country;
        $obj->state = $this->state;
        $obj->city = $this->city;
        return json_encode($obj);
    }

    public function jsonSerialize()
    {
        return $this->toJson();
    }
}