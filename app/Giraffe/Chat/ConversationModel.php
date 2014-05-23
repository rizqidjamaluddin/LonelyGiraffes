<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class ConversationModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'conversations';
	protected $fillable = ['name'];
}