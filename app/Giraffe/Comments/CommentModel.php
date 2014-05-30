<?php  namespace Giraffe\Comments; 

use Eloquent;

class CommentModel extends Eloquent
{
    protected $table = 'comments';
    protected $fillable = ['user_id', 'comment_stream_id', 'body', 'html_body'];

} 