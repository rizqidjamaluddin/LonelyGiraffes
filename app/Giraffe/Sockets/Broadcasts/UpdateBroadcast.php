<?php  namespace Giraffe\Sockets\Broadcasts; 
use Giraffe\Sockets\Broadcast;

class UpdateBroadcast extends Broadcast {
    public function getName()
    {
        return 'update';
    }


} 