<?php  namespace Giraffe\Notifications;

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
     * @param      $user
     *
     * @return \Giraffe\Notifications\NotificationContainerModel[]
     */
    public function getUserNotifications($user)
    {
        $this->gatekeeper->mayI('read', 'notification_container')->please();
        $user = $this->userRepository->getByHash($user);
        $notifications = $this->containerRepository->getForUser($user->id);
        return $notifications;
    }

    /**
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

    public function dismiss($container, UserModel $me)
    {
        /** @var NotificationContainerModel $container */
        $container = $this->containerRepository->getByHash($container);
        $this->gatekeeper->mayI('delete', $container)->please();

        // delete body and container
        $container->notification->delete();
        $container->delete();

        return true;
    }

    public function dismissAll(UserModel $me)
    {
        $this->gatekeeper->mayI('dismiss_all', 'notification_container')->please();

        $notifications = $this->containerRepository->getForUser($me->id);
        foreach ($notifications as $notificationContainer) {
            $notificationContainer->notification->delete();
            $notificationContainer->delete();
        }

        return true;

    }

} 