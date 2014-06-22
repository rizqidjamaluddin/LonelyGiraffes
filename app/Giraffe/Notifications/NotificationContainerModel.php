<?php namespace Giraffe\Notifications;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserModel;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

/**
 * @property $notification Notification
 * @property $user_id      integer
 * @property $destination  UserModel
 */
class NotificationContainerModel extends Eloquent implements TransformableInterface, ProtectedResource
{
    use HasEloquentHash;

    protected $table = 'notification_containers';
    protected $fillable = ['user_id', 'notification_type', 'notification_id', 'hash'];


    public function notification()
    {
        return $this->morphTo();
    }

    public function destination()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new NotificationContainerTransformer();
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'notification_container';
    }

    public function checkOwnership(UserModel $user)
    {
        return $this->destination->id == $user->id;
    }
}