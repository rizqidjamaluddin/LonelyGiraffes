<?php  namespace Giraffe\Notifications; 

use Eloquent;

abstract class Notification extends Eloquent
{
    public function container()
    {
        return $this->morphOne('Giraffe\Notifications\NotificationContainerModel', 'notification');
    }
} 