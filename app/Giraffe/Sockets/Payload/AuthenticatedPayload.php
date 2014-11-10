<?php  namespace Giraffe\Sockets\Payload; 
use Giraffe\Users\UserModel;

abstract class AuthenticatedPayload extends Payload
{

    /**
     * @param UserModel $user
     * @return bool
     */
    abstract public function canAccess(UserModel $user);
} 