<?php  namespace Giraffe\Geolocation; 

use Giraffe\Support\Transformer\Transformer;

class LocationTransformer extends Transformer
{
    /**
     * @param Location $location
     * @return array
     */
    public function transform($location)
    {
        return [
            'humanized' => $location->getHumanizedForm(),
            'city' => $location->city,
            'state' => $location->state,
            'country' => $location->country
        ];
    }
} 