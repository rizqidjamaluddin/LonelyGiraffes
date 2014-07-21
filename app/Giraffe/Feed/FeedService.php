<?php  namespace Giraffe\Feed;

use Giraffe\Common\Service;
use Giraffe\Geolocation\LocationHelper;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserRepository;

class FeedService extends Service
{
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
        Parser $parser,
        LocationHelper $locationHelper,
        PostRepository $postRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->locationHelper = $locationHelper;
        $this->postRepository = $postRepository;
    }

    public function getGlobalFeed($cursor = null)
    {
        $this->gatekeeper->mayI('read', 'feed')->please();
        if ($cursor) {
            $bottomPost = $this->postRepository->getByHash($cursor);
            return $this->postRepository->getGlobalBeforeId($bottomPost->id);
        } else {
            return $this->postRepository->getGlobal();
        }
    }

    public function getGlobalFeedAfter($after)
    {
        $this->gatekeeper->mayI('read', 'feed')->please();
        $topPost = $this->postRepository->getByHash($after);
        return $this->postRepository->getGlobalAfterId($topPost->id);
    }

    public function getPost($post)
    {
        $post = $this->postRepository->getByHash($post);
        $this->gatekeeper->mayI('read', $post)->please();
        return $post;
    }


} 