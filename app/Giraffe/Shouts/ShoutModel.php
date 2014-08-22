<?php namespace Giraffe\Shouts;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Comments\Commentable;
use Giraffe\Feed\Postable;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Users\UserRepository;

/**
 * @property $id int
 * @property $user_id int
 * @property $hash string
 * @property $body string
 * @property $html_body string
 */
class ShoutModel extends Eloquent implements Postable, ProtectedResource, TransformableInterface, Commentable {
    
    use HasEloquentHash;

    protected $table = 'shouts';
	protected $fillable = ['hash', 'user_id', 'body', 'html_body'];

    public function getOwnerId()
    {
        return $this->user_id;
    }

    public function fetchAuthor($userRepository = null)
    {
        if (!$this->user_id) return null;
        /** @var UserRepository $userRepository */
        $userRepository = $userRepository ?: \App::make('Giraffe\Users\UserRepository');
        return $userRepository->getById($this->user_id);
    }

    public function author()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
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
        return $this->user_id == $userModel->id;
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new ShoutTransformer;
    }
}