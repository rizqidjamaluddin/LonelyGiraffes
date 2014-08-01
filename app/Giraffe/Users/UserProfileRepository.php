<?php  namespace Giraffe\Users; 
use Giraffe\Common\EloquentRepository;

class UserProfileRepository extends EloquentRepository
{
    public function __construct(UserProfileModel $userProfileModel)
    {
        parent::__construct($userProfileModel);
    }

    public function getForUserId($user)
    {
        return $this->model->where('user_id', $user)->first();
    }
} 