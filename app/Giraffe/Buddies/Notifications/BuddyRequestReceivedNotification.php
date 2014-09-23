<?php  namespace Giraffe\Buddies\Notifications; 

use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Buddies\Requests\BuddyRequestRepository;
use Giraffe\Common\Transformable;
use Giraffe\Notifications\Notifiable;
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
     * @param BuddyRequestModel $requestModel
     * @return static
     */
    public static function upon(BuddyRequestModel $requestModel)
    {
        $t = new static;
        $t->request = $requestModel;
        $t->buddy_request_id = $requestModel->id;
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
    public static function getMessage()
    {
        // TODO: Implement getMessage() method.
    }
    
    public static function getLinks()
    {
        // TODO: Implement getLinks() method.
    }

    public static function getActions()
    {
        // TODO: Implement getActions() method.
    }

    public static function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    public static function getRead()
    {
        // TODO: Implement getRead() method.
    }
}