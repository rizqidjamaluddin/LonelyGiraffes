<?php
use Giraffe\Common\Controller;
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
        return $notifications;
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