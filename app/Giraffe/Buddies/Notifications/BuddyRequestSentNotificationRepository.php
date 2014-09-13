<?php  namespace Giraffe\Buddies\Notifications; 
use Giraffe\Common\EloquentRepository;

class BuddyRequestSentNotificationRepository extends EloquentRepository
{
    public function __construct(BuddyRequestSentNotification $notification)
    {
        parent::__construct($notification);
    }
} 