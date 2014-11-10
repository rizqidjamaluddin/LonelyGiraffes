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
       $notification = $this->contents;
        $result = $notification->checkOwnership($user);
        return $result;
    }

    public function getContents()
    {
        $transformer = new NotificationTransformer();
        return $transformer->transform($this->contents);
    }
}