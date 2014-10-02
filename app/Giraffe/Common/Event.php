<?php  namespace Giraffe\Common; 
class Event 
{
    public function getName()
    {
        return get_class($this);
    }
}