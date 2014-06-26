<?php  namespace Giraffe\Geolocation; 

interface Locatable
{
    /**
     * @return Location
     */
    public function getLocation();
} 