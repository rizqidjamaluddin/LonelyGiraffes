<?php  namespace Giraffe\Authorization; 

use Giraffe\Users\UserModel;

interface ProtectedResource
{
    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName();

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return UserModel
     */
    public function checkOwnership(UserModel $user);
} 