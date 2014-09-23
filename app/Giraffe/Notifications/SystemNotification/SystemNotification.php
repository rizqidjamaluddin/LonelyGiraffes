<?php  namespace Giraffe\Notifications\SystemNotification;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Common\Transformable;
use Giraffe\Notifications\Notifiable;
use Giraffe\Notifications\SystemNotification\SystemNotificationTransformer;

class SystemNotification implements Notifiable
{

    /**
     * @param      $body
     * @param null $title
     *
     * @return static
     */
    public static function make($body, $title = null)
    {
        $model = new static;
        $model->message = $body;
        $model->title = $title ?: 'System Notification';
        return $model;
    }

    public static function getType()
    {
        return 'system_notification';
    }

}