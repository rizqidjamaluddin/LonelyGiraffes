<?php
use Giraffe\Comments\CommentRepository;
use Giraffe\Comments\CommentTransformer;
use Giraffe\Common\Controller;
use Giraffe\Shouts\ShoutCommentingService;

class ShoutCommentController extends Controller
{
    /**
     * @var Giraffe\Shouts\ShoutCommentingService
     */
    private $commentingService;
    /**
     * @var Giraffe\Comments\CommentRepository
     */
    private $commentRepository;

    public function __construct(ShoutCommentingService $commentingService, CommentRepository $commentRepository)
    {
        $this->commentingService = $commentingService;
        $this->commentRepository = $commentRepository;
        parent::__construct();
    }

    public function index($shout)
    {
        $options = Input::only(['before', 'after', 'take']);
        $options = $this->translateOptions($options);
        $comments = $this->commentingService->getForShout($shout, $options);
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

    protected function translateOptions($options)
    {
        if (!array_get($options, 'take')) {
            $options['take'] = 10;
        }
        if (array_get($options, 'take') > 20 ) {
            $options['take'] = 20;
        }
        if ($before = array_get($options, 'before')) {
            $options['before'] = $this->commentRepository->getByHash($before)->id;
        }
        if ($after = array_get($options, 'after')) {
            $options['after'] = $this->commentRepository->getByHash($after)->id;
        }
        return $options;
    }
} 