<?php namespace Giraffe\Notifications;

use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Common\Value\Hash;
use Giraffe\Notifications\Registry\NotifiableRegistry;
use Giraffe\Users\UserModel;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

/**
 * @property integer    $id
 * @property string     $hash
 * @property string     $notification_type
 * @property integer    $notification_id
 * @property integer    $user_id
 * @property UserModel  $destination
 */
class NotificationModel extends Eloquent implements TransformableInterface, ProtectedResource
{
    use HasEloquentHash;

    protected $table = 'notification_containers';
    protected $fillable = ['user_id', 'notification_type', 'notification_id', 'hash'];

    /**
     * @param Notifiable $notifiable
     * @param UserModel  $user
     * @return static
     */
    public static function generate(Notifiable $notifiable, UserModel $user)
    {
        $instance = new static;
        $instance->notification_type = $notifiable->getType();
        $instance->notification_id = $notifiable->getID();
        $instance->user_id = $user->id;
        $instance->hash = new Hash;
        return $instance;
    }


    /**
     * @return Notifiable
     */
    public function notifiable()
    {
        /** @var NotifiableRegistry $notifiableRegistry */
        $notifiableRegistry = \App::make(NotifiableRegistry::class);

        $repository = $notifiableRegistry->resolveRepository($this->notification_type);
        return $repository->get($this->notification_id);
    }

    public function deleteNotifiable()
    {
        /** @var NotifiableRegistry $notifiableRegistry */
        $notifiableRegistry = \App::make(NotifiableRegistry::class);

        $repository = $notifiableRegistry->resolveRepository($this->notification_type);
        return $repository->deleteById($this->notification_id);
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
        return $this->destination->id == $user->id;
    }
}