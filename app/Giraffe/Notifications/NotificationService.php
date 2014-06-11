<?php  namespace Giraffe\Notifications;

use Giraffe\Users\UserRepository;

class NotificationService
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
        $this->containerRepository = $containerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param      $user
     * @param null $lastTimestamp
     */
    public function getUserNotifications($user, $lastTimestamp = null)
    {

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
        $container = new NotificationContainerModel(['user_id' => $destinationUser->id]);
        $notification->container()->save($container);

        return $container;
    }

} 