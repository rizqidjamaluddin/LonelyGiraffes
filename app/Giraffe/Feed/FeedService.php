<?php  namespace Giraffe\Feed;

use Eloquent;
use Giraffe\Contracts\Postable;
use Giraffe\Geolocation\LocationHelper;
use Giraffe\Parser\Parser;
use Giraffe\Authorization\Gatekeeper;
use Giraffe\Feed\PostRepository;
use Giraffe\Shouts\ShoutRepository;
use Giraffe\Users\UserRepository;

class FeedService
{

    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    private $gatekeeper;
    /**
     * @var \Giraffe\Feed\PostRepository
     */
    private $postRepository;
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Parser\Parser
     */
    private $parser;
    /**
     * @var \Giraffe\Geolocation\LocationHelper
     */
    private $locationHelper;

    public function __construct(
        Gatekeeper $gatekeeper,
        Parser $parser,
        LocationHelper $locationHelper,
        PostRepository $postRepository,
        UserRepository $userRepository
    ) {
        $this->gatekeeper = $gatekeeper;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->locationHelper = $locationHelper;
        $this->postRepository = $postRepository;
    }

    public function addCommentOnPost($user, $post, $comment)
    {

    }

    public function getBuddyFeed($user)
    {

    }

    public function getGlobalFeed($user)
    {

    }

    public function getLocalFeed($user)
    {

    }

    /**
     * @param $post
     *
     * @return Eloquent
     */
    public function getPost($post)
    {
        return $this->postRepository->get($post);
    }
} 