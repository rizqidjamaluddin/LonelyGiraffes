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
    public static function getMessage();

    /**
     * Associative array with transformable entities to attach as links.
     *
     * @return array
     */
    public static function getLinks();

    /**
     * @return NotificationAction[]
     */
    public static function getActions();

    /**
     * Determine phrasing to show to users their response for this notification. E.g. "Accepted", "Declined". Clients should hide action links/buttons if a status is given.
     *
     * @return string
     */
    public static function getStatus();

    /**
     * Additional check to decide if this notification is "read". This works as an OR along with the notification
     * dismissing feature from the wrapper; use it for additional checks, such as declaring a notification as read when
     * a buddy request has been responded to.
     *
     * @return boolean
     */
    public static function getRead();
} 