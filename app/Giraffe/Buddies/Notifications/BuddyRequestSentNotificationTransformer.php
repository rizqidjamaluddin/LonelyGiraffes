<?php  namespace Giraffe\Buddies\Notifications; 

use League\Fractal\TransformerAbstract;

class BuddyRequestSentNotificationTransformer extends TransformerAbstract
{
    public function transform(BuddyRequestSentNotification $notification)
    {
        $attached = $notification->getRequest();
        $attached = $attached->getTransformer()->transform($attached);

        return [
            'request' => $attached
        ];
    }
} 