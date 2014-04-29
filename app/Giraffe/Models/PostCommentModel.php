<?php namespace Giraffe\Models;

use Eloquent;

class PostCommentModel extends Eloquent {
    protected $table = 'post_comments';
	protected $fillable = ['user_id', 'body'];
}