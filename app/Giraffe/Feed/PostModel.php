<?php namespace Giraffe\Feed;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

/**
 * @property UserModel $user
 * @property Postable $postable
 * @property String $hash
 */
class PostModel extends Eloquent implements ProtectedResource, TransformableInterface {
    use HasEloquentHash;

    protected $table = 'posts';
	protected $fillable = ['user_id', 'hash', 'postable_type', 'postable_id', 'city', 'state', 'country', 'lat', 'long', 'cell'];

    public function postable()
    {
        return $this->morphTo();
    }

    public function fetchAuthor($userRepository = null)
    {
        if (!$this->user_id) return null;
        /** @var UserRepository $userRepository */
        $userRepository = $userRepository ?: \App::make(UserRepository::class);
        return $userRepository->getById($this->user_id);
    }

    public function author()
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

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new PostTransformer;
    }
}