<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class ChatroomModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'conversations';
	protected $fillable = ['name'];
}