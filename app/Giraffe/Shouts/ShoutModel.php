<?php namespace Giraffe\Shouts;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Comments\Commentable;
use Giraffe\Comments\CommentModel;
use Giraffe\Comments\CommentStreamRepository;
use Giraffe\Feed\Postable;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Support\Transformer\DefaultTransformable;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Users\UserModel;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Collection;

/**
 * @property $id int
 * @property $user_id int
 * @property $hash string
 * @property $body string
 * @property $html_body string
 */
class ShoutModel extends Eloquent implements Postable, ProtectedResource, Transformable, Commentable, DefaultTransformable {
    
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

    public function addComment($body, $user)
    {
        /** @var CommentStreamRepository $commentStreamRepository */
        $commentStreamRepository = \App::make(CommentStreamRepository::class);

        $stream = $commentStreamRepository->getOrCreateFor($this);
        return $stream->postComment($body, $user);
    }

    public function getComments($options = [])
    {
        /** @var CommentStreamRepository $commentStreamRepository */
        $commentStreamRepository = \App::make(CommentStreamRepository::class);
        $stream = $commentStreamRepository->getFor($this);

        if (!$stream) return new Collection();

        return $stream->getComments($options);
    }

    public function getCommentCount()
    {
        /** @var CommentStreamRepository $commentStreamRepository */
        $commentStreamRepository = \App::make(CommentStreamRepository::class);
        $stream = $commentStreamRepository->getFor($this);

        if (!$stream) return 0;

        return $stream->getCommentCount();
    }

    public function getCommentators()
    {
        /** @var CommentStreamRepository $commentStreamRepository */
        $commentStreamRepository = \App::make(CommentStreamRepository::class);
        $stream = $commentStreamRepository->getFor($this);

        if (!$stream) return new Collection();

        return $stream->getParticipatingUsers();
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

    public function getDefaultTransformer()
    {
        return new ShoutTransformer;
    }

    public function getType()
    {
        return 'shout';
    }
}