<?php  namespace Giraffe\Buddies\Notifications; 

use League\Fractal\TransformerAbstract;

class BuddyRequestSentNotificationTransformer extends TransformerAbstract
{
    public function transform(BuddyRequestSentNotification $notification)
    {
        return [
            'id' => $notification->getRequest()->id
        ];
    }
} 