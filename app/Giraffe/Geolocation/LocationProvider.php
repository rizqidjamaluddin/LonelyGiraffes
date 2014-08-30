<?php  namespace Giraffe\Geolocation;

use Illuminate\Support\Collection;

interface LocationProvider
{
    /**
     * @param $hint
     * @return Collection
     */
    public function search($hint);
}