<?php namespace Giraffe\Models;

use Eloquent;
use Giraffe\Support\HasHashEloquent;

class ConversationModel extends Eloquent {
    use HasHashEloquent;

    protected $table = 'conversations';
	protected $fillable = ['name'];
}