<?php namespace Giraffe\Events;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Comments\Commentable;
use Giraffe\Comments\CommentStreamRepository;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Feed\Postable;
use Giraffe\Sockets\Pipeline;
use Giraffe\Support\Transformer\DefaultTransformable;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserModel;
use Illuminate\Support\Collection;

class EventModel extends Eloquent implements Commentable, Postable, ProtectedResource, Transformable, DefaultTransformable{
    use HasEloquentHash;

    protected $table = 'events';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body', 'url', 'location', 'city', 'state', 'country', 'lat', 'long',
        'cell', 'timestamp'];

    protected $dates = ['updated_at', 'created_at', 'timestamp'];

    public function owner()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
    }

    public function addComment($body, $user)
    {
        /** @var CommentStreamRepository $commentStreamRepository */
        $commentStreamRepository = \App::make(CommentStreamRepository::class);

        $stream = $commentStreamRepository->getOrCreateFor($this);
        $comment = $stream->postComment($body, $user);

        /** @var Pipeline $pipeline */
        $pipeline = \App::make(Pipeline::class);
        $pipeline->issue('/events/' . $this->hash);
        $pipeline->issue('/events/' . $this->hash . '/comments');

        return $comment;
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

    public function getParticipants()
    {
        return [];
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'event';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $user->id === $this->user_id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getOwnerId()
    {
        return $this->user_id;
    }

    public function getType()
    {
        return 'event';
    }

    /**
     * @return Transformer
     */
    public function getDefaultTransformer()
    {
        return new EventTransformer();
    }
}