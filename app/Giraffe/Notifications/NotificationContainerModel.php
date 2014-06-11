<?php namespace Giraffe\Notifications;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class NotificationContainerModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'notification_containers';
	protected $fillable = ['user_id', 'notification_type', 'notification_id'];

    public function notification()
    {
        return $this->morphTo();
    }
}