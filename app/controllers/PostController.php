<?php

use Giraffe\Common\Controller;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\NotImplementedException;
use Giraffe\Feed\FeedService;
use Giraffe\Feed\PostRepository;
use Giraffe\Feed\PostTransformer;

class PostController extends Controller
{

    /**
     * @var FeedService
     */
    private $feedService;
    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(FeedService $feedService, PostRepository $postRepository)
    {
        $this->feedService = $feedService;
        $this->postRepository = $postRepository;
        parent::__construct();
    }

    public function index()
    {
        $filter = new QueryFilter;
        $filter->set('after', Input::get('after'), null, $this->postRepository);
        $filter->set('before', Input::get('before'), null, $this->postRepository);
        $filter->set('take', (int) Input::get('take'), 10, null, [1, 20]);

        if (Input::exists('user')) {
            $results = $this->feedService->getUserPosts(Input::get('user'), $filter);
            return $this->withCollection($results, new PostTransformer(), 'posts');
        }

        $results = $this->feedService->getGlobalFeed($filter);
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