<?php
use Giraffe\Common\Controller;
use Giraffe\Notifications\NotificationContainerTransformer;
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
        $notifications = $this->notificationService->getUserNotifications($this->gatekeeper->me());
        if (count($notifications) == 0) {
            $this->withCollection([], new NotificationContainerTransformer(), 'notifications');
        }
        return $this->withCollection($notifications, new NotificationContainerTransformer(), 'notifications');
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