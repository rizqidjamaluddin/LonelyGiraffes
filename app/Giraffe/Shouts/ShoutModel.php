<?php namespace Giraffe\Shouts;

use Eloquent;
use Giraffe\Feed\Postable;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Giraffe\Authorization\ProtectedResource;

/**
 * @property $id int
 * @property $user_id int
 * @property $hash string
 * @property $body string
 * @property $html_body string
 */
class ShoutModel extends Eloquent implements Postable, ProtectedResource {
    
    use HasEloquentHash;

    protected $table = 'shouts';
	protected $fillable = ['hash', 'user_id', 'body', 'html_body'];

    public function getOwnerId()
    {
        return $this->user_id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * ---------------- Protected Resource ----------------
     */

    public function getResourceName()
    {
        return "shout";
    }

    public function checkOwnership(UserModel $userModel)
    {
        return $this->id == $userModel->id;
    }
}