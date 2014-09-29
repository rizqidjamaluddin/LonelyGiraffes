<?php
use Giraffe\Common\Controller;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Notifications\NotificationTransformer;
use Giraffe\Notifications\NotificationService;

class NotificationController extends Controller
{
    /**
     * @var Giraffe\Notifications\NotificationService
     */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function index()
    {

        $options = new QueryFilter();
        $options->set('only', Input::get('only'), '');
        $options->set('except', Input::get('except'), '');
        $notifications = $this->notificationService->getUserNotifications($this->gatekeeper->me(), $options);
        if (count($notifications) == 0) {
            return $this->withCollection([], new NotificationTransformer(), 'notifications');
        }
        return $this->withCollection($notifications, new NotificationTransformer(), 'notifications');
    }

    public function destroy($notification)
    {
        $result = $this->notificationService->dismiss($notification, $this->gatekeeper->me());
        return ['message' => 'Notification dismissed'];
    }

    public function dismissAll()
    {
        $this->notificationService->dismissAll($this->gatekeeper->me());
        return ['message' => 'Notifications dismissed'];
    }
} 