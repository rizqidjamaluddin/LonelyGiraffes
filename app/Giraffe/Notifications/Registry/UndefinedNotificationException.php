<?php  namespace Giraffe\Notifications\Registry; 
use Giraffe\Common\MyBadException;

class UndefinedNotificationException extends MyBadException
{
    public function __construct()
    {
        parent::__construct("There was something here for you, but it ran before we could catch it. (This is a bug - we're on it now!)");
    }
} 