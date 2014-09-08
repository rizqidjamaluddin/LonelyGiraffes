<?php  namespace Giraffe\Notifications;

use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\Service;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

/**
 * Class NotificationService
 *
 * To issue a notification, instantiate and persist a notifiable class (e.g. NewMessageNotification) in its own
 * repository or mechanism of your choice. Then invoke NotificationService::issue(Notifiable $n, User $u) to
 * generate a wrapper and issue it to the desired user.
 *
 * @package Giraffe\Notifications
 */
class NotificationService extends Service
{

    /**
     * @var NotificationRepository
     */
    private $repository;
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    /**
     * @var Array
     */
    protected $registry = [];

    public function __construct(
        NotificationRepository $repository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get notification containers for a particular user.
     *
     * @param UserModel|string $user
     * @param QueryFilter      $filter
     *
     * @return \Giraffe\Notifications\NotificationModel[]
     */
    public function getUserNotifications($user, QueryFilter $filter)
    {
        $this->gatekeeper->mayI('read', 'notification_container')->please();
        $user = $this->userRepository->getByHash($user);
        $notifications = $this->repository->getForUser($user->id, $filter);
        return $notifications;
    }

    /**
     * @param Notifiable $notifiable
     * @param UserModel  $destinationUser
     * @return NotificationModel
     */
    public function issue(Notifiable $notifiable, UserModel $destinationUser)
    {
        $notification = NotificationModel::generate($notifiable, $destinationUser);
        $this->repository->save($notification);
        return $notification;
    }

    /**
     * Dismiss a notification container as well as the embedded notification.
     *
     * @param NotificationModel|string $container
     * @return bool
     */
    public function dismiss($container)
    {
        /** @var NotificationModel $container */
        $container = $this->repository->getByHash($container);
        $this->gatekeeper->mayI('delete', $container)->please();

        // delete body and container
        $container->notification->delete();
        $container->delete();

        return true;
    }

    /**
     * Dismiss all notifications for a user.
     *
     * @param $user
     * @return bool
     */
    public function dismissAll($user)
    {
        $this->gatekeeper->mayI('dismiss_all', 'notification_container')->please();

        $user = $this->userRepository->getByHash($user);
        $notifications = $this->repository->getForUser($user->id, new QueryFilter());
        foreach ($notifications as $notificationContainer) {
            $notificationContainer->notification->delete();
            $notificationContainer->delete();
        }

        return true;

    }

} 