<?php

use Giraffe\Comments\CommentTransformer;
use Giraffe\Common\Controller;
use Giraffe\Events\EventCommentingService;
use Giraffe\Events\EventRepository;

class EventCommentController extends Controller
{

    /**
     * @var EventRepository
     */
    private $eventRepository;
    /**
     * @var EventCommentingService
     */
    private $eventCommentingService;

    public function __construct(
        EventRepository $eventRepository,
        EventCommentingService $eventCommentingService
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventCommentingService = $eventCommentingService;
        parent::__construct();
    }

    public function index($event)
    {
        $options = Input::only(['before', 'after', 'take']);
        $options = $this->translateOptions($options);
        $comments = $this->eventCommentingService->getForEvent($event, $options);
        return $this->withComments($comments);
    }

    public function store($shout)
    {
        $comment = $this->eventCommentingService->addComment($shout, Input::get('body'), $this->gatekeeper->me());
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
        if (array_get($options, 'take') > 20) {
            $options['take'] = 20;
        }
        if ($before = array_get($options, 'before')) {
            $options['before'] = $this->eventRepository->getByHash($before)->id;
        }
        if ($after = array_get($options, 'after')) {
            $options['after'] = $this->eventRepository->getByHash($after)->id;
        }
        return $options;
    }
} 