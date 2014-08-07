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
        $options = Input::only(['after', 'before', 'take']);

        if (Input::exists('user')) {
            $results = $this->feedService->getUserPosts(Input::get('user'), $options);
            return $this->withCollection($results, new PostTransformer(), 'posts');
        }

        $results = $this->feedService->getGlobalFeed($options);
        return $this->withCollection($results, new PostTransformer, 'posts');
    }

    public function show($post)
    {
        $fetch = $this->feedService->getPost($post);
        return $this->withItem($fetch, new PostTransformer, 'posts');
    }

    public function addComment($post)
    {
        throw new NotImplementedException;
    }
} 