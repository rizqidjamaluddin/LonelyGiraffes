<?php  namespace Giraffe\Notifications\Types; 

use Eloquent;
use Giraffe\Notifications\Notification;

class BuddyRequestNotificationModel extends Notification
{
    protected $table = 'buddy_request_notifications';
    protected $fillable = [];
}