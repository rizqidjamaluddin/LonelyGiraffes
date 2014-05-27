<?php  namespace Giraffe\Events; 

use Giraffe\Common\EloquentRepository;

class EventRepository extends EloquentRepository
{
    public function __construct(EventModel $eventModel)
    {
        parent::__construct($eventModel);
    }
} 