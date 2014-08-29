<?php  namespace Giraffe\Geolocation;

interface LocationProvider
{
    /**
     * @param $hint
     * @return Location[]
     */
    public function search($hint);
}