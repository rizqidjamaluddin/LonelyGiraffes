<?php namespace Giraffe\Notifications;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Common\Value\Hash;
use Giraffe\Notifications\Registry\NotifiableRegistry;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Users\UserModel;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

/**
 * @property integer    $id
 * @property string     $hash
 * @property string     $notification_type
 * @property string     $corpus
 * @property integer    $user_id
 * @property UserModel  $destination
 * @property integer    $read
 */
class NotificationModel extends Eloquent implements Transformable, ProtectedResource
{
    use HasEloquentHash;

    protected $table = 'notification_containers';
    protected $fillable = ['user_id', 'notification_type', 'hash', 'corpus', 'read'];

    /**
     * @param Notifiable $notifiable
     * @param UserModel  $user
     * @return static
     */
    public static function generate(Notifiable $notifiable, UserModel $user)
    {
        $instance = new static;
        $instance->notification_type = $notifiable->getType();
        $instance->user_id = $user->id;
        $instance->hash = new Hash;
        $instance->corpus = serialize($notifiable);
        return $instance;
    }


    /**
     * @return Notifiable
     */
    public function notifiable()
    {
        return unserialize($this->corpus);
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
        return new NotificationTransformer();
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'notification';
    }

    public function checkOwnership(UserModel $user)
    {
        return $this->user_id == $user->id;
    }


    public function markRead()
    {
        $this->read = 1;
    }
}