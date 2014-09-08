<?php  namespace Giraffe\Notifications\SystemNotification; 
use Giraffe\Common\EloquentRepository;

class SystemNotificationRepository extends EloquentRepository
{
    public function __construct(SystemNotificationModel $systemNotificationModel)
    {
        parent::__construct($systemNotificationModel);
    }
} 