<?php  namespace Giraffe\Notifications\Types; 

use Eloquent;
use Giraffe\Notifications\Notification;

class BuddyRequestNotificationModel extends Eloquent implements Notification
{
    protected $table = 'buddy_request_notifications';
    protected $fillable = [];
}