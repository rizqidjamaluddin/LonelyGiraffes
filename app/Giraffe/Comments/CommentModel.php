<?php  namespace Giraffe\Comments; 

use Eloquent;
use Giraffe\Authorization\Gatekeeper;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Common\Value\Hash;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;

class CommentModel extends Eloquent implements ProtectedResource
{

    use HasEloquentHash;

    protected $table = 'comments';
    protected $fillable = ['hash', 'user_id', 'comment_stream_id', 'body', 'html_body'];

    public static function write(CommentStreamModel $commentStream, UserModel $author, $body)
    {
        /** @var Parser $parser */
        $parser = \App::make(Parser::class);

        /** @var CommentValidator $commentValidator */
        $commentValidator = \App::make(CommentValidator::class);

        $commentValidator->validate($body);
        $body = substr($body, 0, 1000);

        $instance = new static;
        $instance->hash = new Hash();
        $instance->user_id = $author->id;
        $instance->comment_stream_id = $commentStream->id;
        $instance->body = $body;
        $instance->html_body = $parser->parseComment($body);

        /** @var Gatekeeper $gatekeeper */
        $gatekeeper = \App::make(Gatekeeper::class);
        $gatekeeper->mayI('create', $instance)->please();

        return $instance;
    }

    public function author()
    {
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        return $userRepository->get($this->user_id);
    }

    /**
     * Lowercase name of this resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'comment';
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $this->user_id == $user->id;
    }
}