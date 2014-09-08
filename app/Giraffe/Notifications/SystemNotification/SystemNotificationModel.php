<?php  namespace Giraffe\Notifications\SystemNotification;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Common\Transformable;
use Giraffe\Notifications\Notifiable;
use Giraffe\Notifications\SystemNotification\SystemNotificationTransformer;

class SystemNotificationModel extends Eloquent implements Transformable, Notifiable
{
    protected $table = 'system_notifications';
    protected $fillable = ['title', 'message'];

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

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new SystemNotificationTransformer();
    }

    public static function getType()
    {
        return 'system_notification';
    }

    public function getID()
    {
        return $this->id;
    }
}