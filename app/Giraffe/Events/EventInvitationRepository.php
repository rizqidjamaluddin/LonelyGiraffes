<?php  namespace Giraffe\Events; 

use Giraffe\Common\EloquentRepository;

class EventInvitationRepository extends EloquentRepository
{

    public function __construct(EventInvitationModel $eventInvitationModel)
    {
        parent::__construct($eventInvitationModel);
    }
} 