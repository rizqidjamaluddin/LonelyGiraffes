<?php  namespace Giraffe\Notifications;

use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\NotImplementedException;
use Giraffe\Common\Service;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Str;

class NotificationService extends Service
{

    /**
     * @var NotificationContainerRepository
     */
    private $containerRepository;
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    public function __construct(
        NotificationContainerRepository $containerRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->containerRepository = $containerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Get notification containers for a particular user.
     *
     * @param UserModel|string $user
     *
     * @return \Giraffe\Notifications\NotificationContainerModel[]
     */
    public function getUserNotifications($user, QueryFilter $filter)
    {
        $this->gatekeeper->mayI('read', 'notification_container')->please();
        $user = $this->userRepository->getByHash($user);
        $notifications = $this->containerRepository->getForUser($user->id, $filter);
        return $notifications;
    }

    /**
     * Send a notification to a user. Build a class that extends Notification, with all the context relevant to that
     * notification, and queue it using this method.
     *
     * @param Notification $notification
     * @param              $destinationUser
     *
     * @return NotificationContainerModel
     */
    public function queue(Notification $notification, $destinationUser)
    {
        $destinationUser = $this->userRepository->getByHash($destinationUser);
        $notification->save();
        $container = new NotificationContainerModel(
            [
                'user_id' => $destinationUser->id,
                'hash'    => Str::random(32)
            ]
        );
        $notification->container()->save($container);

        return $container;
    }

    /**
     * Dismiss a notification container as well as the embedded notification.
     *
     * @param NotificationContainerModel|string $container
     * @return bool
     */
    public function dismiss($container)
    {
        /** @var NotificationContainerModel $container */
        $container = $this->containerRepository->getByHash($container);
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
        $notifications = $this->containerRepository->getForUser($user->id, new QueryFilter());
        foreach ($notifications as $notificationContainer) {
            $notificationContainer->notification->delete();
            $notificationContainer->delete();
        }

        return true;

    }

} 