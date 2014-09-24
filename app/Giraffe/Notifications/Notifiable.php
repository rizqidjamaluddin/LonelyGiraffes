<?php  namespace Giraffe\Notifications;

use Illuminate\Support\Collection;
use NotificationAction;

interface Notifiable
{

    /**
     * @return string
     */
    public static function getType();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * Associative array with transformable entities to attach as links.
     *
     * @return array
     */
    public function getLinks();

    /**
     * @return NotificationAction[]
     */
    public function getActions();

    /**
     * Additional check to decide if this notification is "read". This works as an OR along with the notification
     * dismissing feature from the wrapper; use it for additional checks, such as declaring a notification as read when
     * a buddy request has been responded to.
     *
     * @return boolean
     */
    public function getRead();
} 