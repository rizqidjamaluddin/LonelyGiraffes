<?php  namespace Giraffe\Notifications;

class NotificationService
{

    /**
     * @var NotificationContainerRepository
     */
    private $containerRepository;

    public function __construct(NotificationContainerRepository $containerRepository)
    {

        $this->containerRepository = $containerRepository;
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
        $notification->save();
        $container = $this->containerRepository->create([]);
    }

} 