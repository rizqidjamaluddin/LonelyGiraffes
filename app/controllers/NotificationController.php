<?php
use Giraffe\Common\Controller;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Notifications\NotificationRepository;
use Giraffe\Notifications\NotificationTransformer;
use Giraffe\Notifications\NotificationService;

class NotificationController extends Controller
{
    /**
     * @var Giraffe\Notifications\NotificationService
     */
    private $notificationService;
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    public function __construct(NotificationService $notificationService, NotificationRepository $notificationRepository)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->notificationRepository = $notificationRepository;
    }

    public function index($user)
    {

        $options = new QueryFilter();
        $options->set('only', Input::get('only'), '');
        $options->set('except', Input::get('except'), '');
        $options->set('after', Input::get('after'), null, $this->notificationRepository);
        $options->set('before', Input::get('before'), null, $this->notificationRepository);
        $options->set('take', (int) Input::get('take'), 10, null, [1, 20]);

        if (Input::exists('unread')) {
            $notifications = $this->notificationService->getUnreadUserNotifications($user, $options);
        } else {
            $notifications = $this->notificationService->getUserNotifications($user, $options);
        }

        if (count($notifications) == 0) {
            return $this->withCollection([], new NotificationTransformer(), 'notifications');
        }
        return $this->withCollection($notifications, new NotificationTransformer(), 'notifications');
    }

    public function dismiss($user, $notification)
    {
        $result = $this->notificationService->dismiss($notification, $user);
        return ['message' => 'Notification dismissed'];
    }

    public function dismissAll($user)
    {
        $this->notificationService->dismissAll($user);
        return ['message' => 'Notifications dismissed'];
    }
} 