<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ChatMessageModel extends Eloquent {
    use HasEloquentHash, SoftDeletingTrait;

    protected $table = 'chat_messages';
	protected $fillable = ['user_id', 'body', 'html_body'];
}