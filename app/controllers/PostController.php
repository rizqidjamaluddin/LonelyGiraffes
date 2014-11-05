<?php

use Giraffe\Common\Controller;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\NotImplementedException;
use Giraffe\Feed\FeedService;
use Giraffe\Feed\PostRepository;
use Giraffe\Feed\PostTransformer;
use Giraffe\Users\UserRepository;

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
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(FeedService $feedService, PostRepository $postRepository, UserRepository $userRepository)
    {
        $this->feedService = $feedService;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
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

        if (Input::exists('buddies')) {
            if (!$user = Input::get('buddies')) {
                $user = $this->gatekeeper->me();
            }
            $results = $this->feedService->getBuddyPosts($user, $filter);
            return $this->withCollection($results, new PostTransformer(), 'posts');
        }

        if (Input::exists('nearby')) {
            if (!$user = Input::get('nearby')) {
                $user = $this->gatekeeper->me();
            }
            $results = $this->feedService->getNearbyPosts($user, $filter);
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