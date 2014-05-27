<?php  namespace Giraffe\Events; 

use Giraffe\Common\EloquentRepository;

class EventAttendeeRepository extends EloquentRepository
{
    public function __construct(EventAttendeeModel $eventAttendeeModel)
    {
        parent::__construct($eventAttendeeModel);
    }
} 