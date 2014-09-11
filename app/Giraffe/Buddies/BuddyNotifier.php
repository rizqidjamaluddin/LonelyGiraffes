<?php  namespace Giraffe\Buddies;

use Giraffe\Buddies\Events\BuddyRequestSentEvent;
use Giraffe\Common\EventListener;
use Giraffe\Common\EventRelay;

class BuddyNotifier implements EventListener
{
    public function subscribe(EventRelay $relay)
    {
        $relay->on(BuddyRequestSentEvent::class, function() {
                dd('trigger!');
            });
    }
} 