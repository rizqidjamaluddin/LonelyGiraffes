<?php  namespace Giraffe\Events; 
use Giraffe\Common\EloquentRepository;

class EventParticipantRepository extends  EloquentRepository
{
    public function __construct(EventParticipantModel $model)
    {
        parent::__construct($model);
    }

    public function getForEvent(EventModel $event)
    {
        $t = $this->model->where('event_id', $event->id)->get();
        return $t;
    }
} 