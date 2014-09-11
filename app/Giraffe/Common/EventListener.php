<?php  namespace Giraffe\Common; 
interface EventListener
{
    public function subscribe(EventRelay $relay);
} 