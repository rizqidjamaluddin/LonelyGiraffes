<?php

use Giraffe\Common\Controller;
use Giraffe\Common\NotImplementedException;
use Giraffe\Feed\FeedService;
use Giraffe\Feed\PostTransformer;

class PostController extends Controller
{

    /**
     * @var FeedService
     */
    private $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
        parent::__construct();
    }

    public function index()
    {
        $results = $this->feedService->getGlobalFeed(Input::get('page'));
        return $this->withCollection($results, new PostTransformer, 'posts');
    }

    public function show($post)
    {

    }

    public function addComment($post)
    {
        throw new NotImplementedException;
    }
} 