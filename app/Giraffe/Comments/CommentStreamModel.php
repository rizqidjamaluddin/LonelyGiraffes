<?php  namespace Giraffe\Comments; 

use Eloquent;

class CommentStreamModel extends Eloquent
{
    protected $table = "comment_streams";
    protected $fillable = ['commentable_type', 'commentable_id'];
} 