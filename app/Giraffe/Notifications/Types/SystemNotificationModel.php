<?php  namespace Giraffe\Notifications\Types; 

use Eloquent;
use Giraffe\Notifications\Notification;

class SystemNotificationModel extends Notification
{
    protected $table = 'system_notifications';
    protected $fillable = ['title', 'message'];

} 