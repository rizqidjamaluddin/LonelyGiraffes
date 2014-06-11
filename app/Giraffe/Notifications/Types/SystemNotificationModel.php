<?php  namespace Giraffe\Notifications\Types; 

use Eloquent;
use Giraffe\Notifications\Notification;

class SystemNotificationModel extends Eloquent implements Notification
{
    protected $table = 'service_notifications';
    protected $fillable = ['title', 'message'];

    public function container()
    {
        return $this->morphOne('Notification', 'notification');
    }
} 