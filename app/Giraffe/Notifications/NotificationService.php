<?php  namespace Giraffe\Notifications;

use Giraffe\Notifications\Support\NotificationPayload;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Service;
use Giraffe\Sockets\Broadcasts\Broadcast;
use Giraffe\Sockets\Pipeline;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('read_notifications', $user)->please();
        $notifications = $this->repository->getForUser($user->id, $filter);
        return $notifications;
    }

    /**
     * @param             $user
     * @param QueryFilter $filter
     * @return mixed
     * @throws \Giraffe\Common\ConfigurationException
     */
    public function getUnreadUserNotifications($user, QueryFilter $filter)
    {
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('read_notifications', $user)->please();
        $notifications = $this->repository->getUnreadForUser($user->id, $filter);
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

        /** @var Pipeline $pipeline */
        $pipeline = \App::make(Pipeline::class);
        $pipeline->dispatch(new Broadcast("/users/{$destinationUser->hash}/notifications", 'update', new NotificationPayload($notification)));

        return $notification;
    }

    /**
     * Dismiss a notification container as well as the embedded notification.
     *
     * @param NotificationModel|string $notification
     * @return bool
     */
    public function dismiss($notification)
    {
        /** @var NotificationModel $notification */
        $notification = $this->repository->getByHash($notification);
        $this->gatekeeper->mayI('delete', $notification)->please();

        $notification->markRead();

        // delete body and container
        $this->repository->save($notification);

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
        $this->gatekeeper->mayI('dismiss_all', 'notification')->please();

        $user = $this->userRepository->getByHash($user);
        $notifications = $this->repository->getForUser($user->id, new QueryFilter());
        foreach ($notifications as $notificationContainer) {
            $notificationContainer->markRead();
            $this->repository->save($notificationContainer);
        }

        return true;

    }

} 