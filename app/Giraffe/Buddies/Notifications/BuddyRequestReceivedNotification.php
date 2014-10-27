<?php  namespace Giraffe\Buddies\Notifications;

use Giraffe\Buddies\Notifications\Support\NotificationAction;
use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Buddies\Requests\BuddyRequestRepository;
use Giraffe\Buddies\Requests\BuddyRequestTransformer;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Notifications\Notifiable;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

/**
 * Class BuddyRequestReceivedNotification
 *
 * @package Giraffe\Buddies\Notifications
 */
class BuddyRequestReceivedNotification implements Notifiable
{
    /**
     * @var string
     */
    public static $type = 'new_buddy_request';

    /**
     * @var int
     */
    public $buddy_request_id;

    /**
     * @var int
     */
    public $sender_id;

    /**
     * @var
     */
    public $timestamp;

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

        /** @var BuddyRequestRepository $buddyRequestRepository */
        $buddyRequestRepository = \App::make(BuddyRequestRepository::class);

        try {
            $actions = (new BuddyRequestTransformer())->generateActions(
                $buddyRequestRepository->getById($this->buddy_request_id)
            );
        } catch (NotFoundModelException $e) {
            // if the request is gone, then no more actions are applicable
            return [];
        }

        return $actions;
    }

    public function getRead()
    {

        /** @var BuddyRequestRepository $buddyRequestRepository */
        $buddyRequestRepository = \App::make(BuddyRequestRepository::class);

        try {
            $buddyRequestRepository->getById($this->buddy_request_id);
        } catch (NotFoundModelException $e) {
            return true;
        }

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