<?php
use Giraffe\Comments\CommentTransformer;
use Giraffe\Common\Controller;
use Giraffe\Shouts\ShoutCommentingService;

class ShoutCommentController extends Controller
{
    /**
     * @var Giraffe\Shouts\ShoutCommentingService
     */
    private $commentingService;

    public function __construct(ShoutCommentingService $commentingService)
    {
        $this->commentingService = $commentingService;
        parent::__construct();
    }

    public function index($shout)
    {
        $comments = $this->commentingService->getForShout($shout);
        return $this->withComments($comments);
    }

    public function store($shout)
    {
        $comment = $this->commentingService->addComment($shout, Input::get('body'), $this->gatekeeper->me());
        return $this->withComment($comment);
    }

    /**
     * @param $comments
     * @return \Illuminate\Http\Response
     */
    protected function withComments($comments)
    {
        return $this->withCollection($comments, new CommentTransformer(), 'comments');
    }

    /**
     * @param $comment
     * @return \Illuminate\Http\Response
     */
    protected function withComment($comment)
    {
        return $this->withItem($comment, new CommentTransformer(), 'comments');
    }
} 