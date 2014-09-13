<?php  namespace Giraffe\Buddies\Notifications; 

use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Buddies\Requests\BuddyRequestRepository;
use Giraffe\Common\Transformable;
use Giraffe\Notifications\Notifiable;

class BuddyRequestSentNotification extends \Eloquent implements Notifiable, Transformable
{

    protected $fillable = ['buddy_request_id'];
    protected static $type = 'new_buddy_request';

    /**
     * @var BuddyRequestModel
     */
    protected $request;

    public static function upon(BuddyRequestModel $requestModel)
    {
        $t = new static;
        $t->request = $requestModel;
        $t->buddy_request_id = $requestModel->id;
        return $t;
    }

    public static function getType()
    {
        return self::$type;
    }

    public function getID()
    {
        return $this->id;
    }

    /**
     * @return BuddyRequestModel
     */
    public function getRequest()
    {
        if ($this->request) {
            return $this->request;
        }

        /** @var BuddyRequestRepository $buddyRequestRepository */
        $buddyRequestRepository = \App::make(BuddyRequestRepository::class);

        return $this->request = $buddyRequestRepository->getById($this->buddy_request_id);
    }

    public function getTransformer()
    {
        return new BuddyRequestSentNotificationTransformer();
    }
}