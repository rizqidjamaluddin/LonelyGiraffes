<?php  namespace Giraffe\Services;

use Eloquent;
use Giraffe\Helpers\Geolocation\LocationHelper;
use Giraffe\Helpers\Parser\Parser;
use Giraffe\Helpers\Rights\Gatekeeper;
use Giraffe\Models\PostModel;
use Giraffe\Models\UserModel;
use Giraffe\Repositories\PostRepository;
use Giraffe\Repositories\ShoutRepository;
use Giraffe\Repositories\UserRepository;

class FeedService
{

    /**
     * @var \Giraffe\Helpers\Rights\Gatekeeper
     */
    private $gatekeeper;
    /**
     * @var \Giraffe\Repositories\PostRepository
     */
    private $postRepository;
    /**
     * @var \Giraffe\Repositories\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Repositories\ShoutRepository
     */
    private $shoutRepository;
    /**
     * @var \Giraffe\Helpers\Parser\Parser
     */
    private $parser;
    /**
     * @var \Giraffe\Helpers\Geolocation\LocationHelper
     */
    private $locationHelper;

    public function __construct(
        Gatekeeper $gatekeeper,
        Parser $parser,
        LocationHelper $locationHelper,
        PostRepository $postRepository,
        UserRepository $userRepository,
        ShoutRepository $shoutRepository
    ) {
        $this->gatekeeper = $gatekeeper;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;
        $this->parser = $parser;
        $this->locationHelper = $locationHelper;
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