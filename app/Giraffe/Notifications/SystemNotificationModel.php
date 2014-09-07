<?php  namespace Giraffe\Notifications;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Notifications\Notification;

class SystemNotificationModel extends Notification implements TransformableInterface
{
    protected $morphClass = "system_notification";
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

    public function getBody()
    {
        return $this->message;
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
}