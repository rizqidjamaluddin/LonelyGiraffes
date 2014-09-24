<?php  namespace Giraffe\Buddies\Notifications; 

use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Buddies\Requests\BuddyRequestRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Transformable;
use Giraffe\Notifications\Notifiable;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use NotificationAction;

/**
 * Class BuddyRequestReceivedNotification
 *
 * @package Giraffe\Buddies\Notifications
 */
class BuddyRequestReceivedNotification  implements Notifiable
{
    /**
     * @var string
     */
    protected static $type = 'new_buddy_request';

    /**
     * @var int
     */
    protected $buddy_request_id;

    /**
     * @var int
     */
    protected $sender_id;

    /**
     * @var
     */
    protected $timestamp;

    /**
     * @param BuddyRequestModel $requestModel
     * @return static
     */
    public static function upon(BuddyRequestModel $requestModel)
    {
        $t = new static;
        $t->buddy_request_id = $requestModel->id;
        $t->sender_id = $requestModel->sender()->id;
        $t->timestamp = $requestModel->created_at;
        return $t;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return self::$type;
    }



    /**
     * @return string
     */
    public function getMessage()
    {
        $sender = $this->getSender();

        return "{$sender->name} sent you a buddy request!";
    }
    
    public function getLinks()
    {
        $sender = $this->getSender();
        if (!$sender) {
            return [];
        }

        return [
            'sender' => $sender
        ];
    }

    public function getActions()
    {
        return [];
    }

    public function getRead()
    {
        return false;
    }

    /**
     * @return UserModel|null
     */
    protected function getSender()
    {
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        try {
            $sender = $userRepository->getById($this->sender_id);
        } catch (NotFoundModelException $e) {
            $sender = null;
        }

        return $sender;
    }
}