<?php  namespace Giraffe\Events; 

use Giraffe\Common\EloquentRepository;

class EventRequestRepository extends EloquentRepository
{
    public function __construct(EventRequestModel $eventRequestModel)
    {
        parent::__construct($eventRequestModel);
    }
} 