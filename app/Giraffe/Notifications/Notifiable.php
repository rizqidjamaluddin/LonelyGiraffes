<?php  namespace Giraffe\Notifications; 

interface Notifiable
{
    public static function getType();
    public function getID();
} 