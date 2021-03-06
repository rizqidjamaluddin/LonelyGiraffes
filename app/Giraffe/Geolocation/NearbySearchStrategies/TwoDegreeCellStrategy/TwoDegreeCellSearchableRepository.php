<?php  namespace Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy;

use Giraffe\Geolocation\NearbySearchableRepository;

interface TwoDegreeCellSearchableRepository extends NearbySearchableRepository
{
    public function twoDegreeCellSearch(array $cell, $options = []);
} 