<?php  namespace Giraffe\Buddies;

use Giraffe\Users\UserModel;

class BuddyService
{
    /**
     * @var \Giraffe\Users\UserModel
     */
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function acceptBuddyRequest($user, $request)
    {
        /** @var $user UserModel */
        $this->userModel->instantiate($user);

    }

    public function createBuddyRequest($user, $destination)
    {

    }

    public function denyBuddyRequest($request)
    {

    }

    public function getUserBuddies($user)
    {

    }

    public function unbuddy($user, $buddy)
    {

    }
} 