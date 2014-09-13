<?php  namespace Giraffe\Buddies;

use Giraffe\Buddies\Events\BuddyRequestSentEvent;
use Giraffe\Buddies\Notifications\BuddyRequestSentNotification;
use Giraffe\Buddies\Notifications\BuddyRequestSentNotificationRepository;
use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Common\EventListener;
use Giraffe\Common\EventRelay;
use Giraffe\Notifications\NotificationService;
use Giraffe\Users\UserModel;

class BuddyNotifier implements EventListener
{

    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function issueBuddyRequestNotification(BuddyRequestModel $requestModel, UserModel $recipient)
    {
        /** @var BuddyRequestSentNotificationRepository $repo */
        $repo = \App::make(BuddyRequestSentNotificationRepository::class);

        $notification = $repo->save(BuddyRequestSentNotification::upon($requestModel));
        $this->notificationService->issue($notification, $recipient);
    }

    public function subscribe(EventRelay $relay)
    {
        $relay->on(BuddyRequestSentEvent::class, function(BuddyRequestSentEvent $event) {
                $this->issueBuddyRequestNotification($event->getBuddyRequest(), $event->getRecipient());
            });
    }
} 