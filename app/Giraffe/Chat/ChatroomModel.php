<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ChatroomModel extends Eloquent {
    use HasEloquentHash, SoftDeletingTrait;

    protected $table = 'chatrooms';
	protected $fillable = ['name', 'hash'];
}