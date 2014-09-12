<?php  namespace Giraffe\Buddies;

use Giraffe\Buddies\Events\BuddyRequestSentEvent;
use Giraffe\Common\EventListener;
use Giraffe\Common\EventRelay;
use Giraffe\Users\UserModel;

class BuddyNotifier implements EventListener
{

    protected function issueBuddyRequestNotification(UserModel $recipient)
    {
        // dd('recipient: ' . $recipient->email);
    }

    public function subscribe(EventRelay $relay)
    {
        $relay->on(BuddyRequestSentEvent::class, function(BuddyRequestSentEvent $event) {
                $this->issueBuddyRequestNotification($event->getRecipient());
            });
    }
} 