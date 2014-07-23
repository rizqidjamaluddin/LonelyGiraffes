<?php namespace Giraffe\Chat;

use Eloquent;

class ChatMessageModel extends Eloquent {
    protected $table = 'conversation_messages';
	protected $fillable = ['user_id', 'body', 'html_body'];
}