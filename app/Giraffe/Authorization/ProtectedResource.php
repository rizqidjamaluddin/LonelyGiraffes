<?php  namespace Giraffe\Authorization; 

use Giraffe\Users\UserModel;

interface ProtectedResource
{
    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @return UserModel
     */
    public function getOwner();
} 