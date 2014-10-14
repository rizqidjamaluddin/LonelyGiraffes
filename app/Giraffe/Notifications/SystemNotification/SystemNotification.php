<?php  namespace Giraffe\Notifications\SystemNotification;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Common\Value\ApiAction;
use Giraffe\Notifications\Notifiable;
use Giraffe\Notifications\SystemNotification\SystemNotificationTransformer;

class SystemNotification implements Notifiable
{

    /**
     * @var string
     */
    protected $message;

    /**
     * @param      $body
     *
     * @return static
     */
    public static function make($body)
    {
        $model = new static;
        $model->message = $body;
        return $model;
    }

    public static function getType()
    {
        return 'system_notification';
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Associative array with transformable entities to attach as links.
     *
     * @return array
     */
    public function getLinks()
    {
        return [];
    }

    /**
     * @return ApiAction[]
     */
    public function getActions()
    {
        return [];
    }

    /**
     * Additional check to decide if this notification is "read". This works as an OR along with the notification
     * dismissing feature from the wrapper; use it for additional checks, such as declaring a notification as read when
     * a buddy request has been responded to.
     *
     * @return boolean
     */
    public function getRead()
    {
        // TODO: Implement getRead() method.
    }
}