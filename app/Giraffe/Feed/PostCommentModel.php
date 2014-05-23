<?php namespace Giraffe\Feed;

use Eloquent;

class PostCommentModel extends Eloquent {
    protected $table = 'post_comments';
	protected $fillable = ['user_id', 'body', 'html_body'];
}