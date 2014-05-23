<?php namespace Giraffe\Notifications;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class NotificationModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'notifications';
	protected $fillable = ['user_id', 'service', 'message', 'metadata'];
}