<?php namespace Giraffe\Chat;

use Eloquent;

class ConversationMessageModel extends Eloquent {
    protected $table = 'conversation_messages';
	protected $fillable = ['user_id', 'body', 'html_body'];
}