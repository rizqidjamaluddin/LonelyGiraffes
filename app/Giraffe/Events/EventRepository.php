<?php  namespace Giraffe\Events; 

use Giraffe\Common\EloquentRepository;
use Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchableRepository;

class EventRepository extends EloquentRepository implements TwoDegreeCellSearchableRepository
{
    public function __construct(EventModel $eventModel)
    {
        parent::__construct($eventModel);
    }

    public function twoDegreeCellSearch(array $cell, $options = [])
    {
        $limit = array_key_exists('limit', $options) ? $options['limit'] : 10;
        $skip = array_key_exists('skip', $options) ? $options['skip'] : 0;

        return $this->model->whereIn('cell', $cell)->take($limit)->skip($skip)->get();
    }
}