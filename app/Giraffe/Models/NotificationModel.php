<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class NotificationModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'notifications';
	protected $fillable = ['user_id', 'service', 'message', 'metadata'];
}