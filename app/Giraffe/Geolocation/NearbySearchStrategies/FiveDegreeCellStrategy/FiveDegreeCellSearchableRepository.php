<?php  namespace Giraffe\Geolocation\NearbySearchStrategies\FiveDegreeCellStrategy; 

use Giraffe\Geolocation\NearbySearchableRepository;

interface FiveDegreeCellSearchableRepository extends NearbySearchableRepository
{
    public function FiveDegreeCellSearch($cell, $limit);
} 