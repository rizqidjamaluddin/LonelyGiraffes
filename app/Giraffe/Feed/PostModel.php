<?php namespace Giraffe\Feed;

use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;

/**
 * @property $user UserModel
 * @property $postable mixed
 * @property $hash string
 */
class PostModel extends Eloquent implements ProtectedResource {
    use HasEloquentHash;

    protected $table = 'posts';
	protected $fillable = ['user_id', 'hash', 'postable_type', 'postable_id', 'city', 'state', 'country', 'lat', 'long', 'cell'];

    public function postable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
    }

    public function getResourceName()
    {
        return "post";
    }

    public function checkOwnership(UserModel $userModel)
    {
        return $this->user->id == $userModel->id;
    }
}