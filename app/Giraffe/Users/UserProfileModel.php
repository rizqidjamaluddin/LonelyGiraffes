<?php  namespace Giraffe\Users; 
use Eloquent;
use Giraffe\Authorization\ProtectedResource;

class UserProfileModel extends Eloquent implements ProtectedResource
{
    protected $table = 'user_profiles';
    protected $fillable = ['bio', 'html_bio'];

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'profile';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $this->user_id == $user->id;
    }
}