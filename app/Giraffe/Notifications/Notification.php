<?php  namespace Giraffe\Notifications; 

use Eloquent;

abstract class Notification extends Eloquent
{
    public function container()
    {
        return $this->morphOne('Giraffe\Notifications\NotificationContainerModel', 'notification');
    }

    abstract public function getBody();

    public function getClass()
    {
        $name = snake_case(class_basename(get_class($this)));
        // remove _model off the end, if applicable
        if (substr($name, -6, 6) == '_model') {
            $name = substr($name, 0, count($name) - 7);
        }
        return $name;
    }
} 