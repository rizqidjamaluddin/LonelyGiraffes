<?php namespace Giraffe\Events;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Feed\Postable;
use Giraffe\Users\UserModel;

class EventModel extends Eloquent implements Postable, ProtectedResource, TransformableInterface{
    use HasEloquentHash;

    protected $table = 'events';
	protected $fillable = ['hash', 'user_id', 'name', 'body', 'html_body', 'url', 'location', 'city', 'state', 'country', 'lat', 'long',
        'cell', 'timestamp'];

    public function owner()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
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
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new EventTransformer();
    }
}