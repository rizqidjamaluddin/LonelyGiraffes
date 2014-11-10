<?php  namespace Giraffe\Buddies\Notifications\Support; 
use Giraffe\Notifications\NotificationModel;
use Giraffe\Notifications\NotificationTransformer;
use Giraffe\Sockets\Payload\AuthenticatedPayload;
use Giraffe\Users\UserModel;

class NotificationPayload extends AuthenticatedPayload
{

    /**
     * @param UserModel $user
     * @return bool
     */
    public function canAccess(UserModel $user)
    {
        /** @var NotificationModel $notification */
        $notification = $this->getContents();
        return $notification->checkOwnership($user);
    }

    public function getContents()
    {
        $transformer = new NotificationTransformer();
        return $transformer->transform($this->contents);
    }
}