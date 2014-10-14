<?php  namespace Giraffe\Geolocation; 

use League\Fractal\TransformerAbstract;

class LocationTransformer extends TransformerAbstract
{
    public function transform(Location $location)
    {
        return [
            'humanized' => $location->getHumanizedForm(),
            'city' => $location->city,
            'state' => $location->state,
            'country' => $location->country
        ];
    }
} 