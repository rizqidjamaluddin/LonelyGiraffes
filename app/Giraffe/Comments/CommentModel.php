<?php  namespace Giraffe\Comments; 

use Eloquent;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserModel;

class CommentModel extends Eloquent
{
    protected $table = 'comments';
    protected $fillable = ['user_id', 'comment_stream_id', 'body', 'html_body'];

    public static function write(CommentStreamModel $commentStream, UserModel $author, $body)
    {
        /** @var Parser $parser */
        $parser = \App::make(Parser::class);

        $instance = new static;
        $instance->user_id = $author->id;
        $instance->comment_stream_id = $commentStream->id;
        $instance->body = $body;
        $instance->html_body = $parser->parseComment($body);

        return $instance;
    }
} 