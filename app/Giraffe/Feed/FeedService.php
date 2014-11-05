<?php  namespace Giraffe\Feed;

use Cache;
use Giraffe\Authorization\GatekeeperException;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Common\Service;
use Giraffe\Geolocation\LocationHelper;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\UnlocatableModelException;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserRepository;
use Giraffe\Users\UserService;
use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Collection;

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
     * @var \Illuminate\Cache\Repository
     */
    private $cache;
    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(
        Parser $parser,
        PostRepository $postRepository,
        UserRepository $userRepository,
        Repository $cache,
        LocationService $locationService
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->postRepository = $postRepository;
        $this->cache = $cache;
        $this->locationService = $locationService;
    }

    public function getGlobalFeed(QueryFilter $options)
    {
        $this->gatekeeper->mayI('read', 'feed')->please();

        if ($options->any()) {
            return $this->postRepository->getGlobal($options);
        }

        // only update the top post cache if it was called without options
        $fetch = $this->postRepository->getGlobal($options);
        $this->setTopPostCache($fetch->first());
        return $fetch;
    }

    /**
     * Set a post as the current topmost post in the feed; eliminates excess DB queries when there isn't a new post
     * present. This should be set by users fetching the global feed, and invalidate when a user posts a new post.
     *
     * @param PostModel $post
     */
    protected function setTopPostCache(PostModel $post)
    {
        $this->cache->put('feed.top', $post->hash, 10);
    }

    /**
     * Invalidate the top post cache. It does not attempt to set the new "top post" to avoid race conditions; instead
     * it relies on users loading the global feed that should set this.
     */
    public function invalidateTopPostCache()
    {
        $this->cache->getStore()->forget('feed.top');
    }

    public function getPost($post)
    {
        $post = $this->postRepository->getByHash($post);
        $this->gatekeeper->mayI('read', $post)->please();
        return $post;
    }

    public function getUserPosts($user, QueryFilter $options)
    {
        $user = $this->userRepository->getByHash($user);
        // no specific gatekeeper check here; adjust PostRepository call if policy changes
        $this->gatekeeper->mayI('read', 'posts')->please();
        return $this->postRepository->getForUser($user->id, $options);
    }

    public function getBuddyPosts($user, QueryFilter $filter)
    {
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('read_buddies', 'posts')->please();

        if ($this->gatekeeper->me()->id != $user->id) {
            throw new GatekeeperException;
        }

        $buddies = $user->getBuddies();

        // fail early if there are no buddies
        if (count($buddies) == 0) {
            return [];
        }
        return $this->postRepository->getForUsers($buddies, $filter);
    }

    public function getNearbyPosts($user, $filter)
    {
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('read_nearby', 'posts')->please();

        if ($this->gatekeeper->me()->id != $user->id) {
            throw new GatekeeperException;
        }

        // fail early if user has no location
        try {
            $user->getLocation();
        } catch (UnlocatableModelException $e) {
            return [];
        }

        /** @var UserService $userService */
        $userService = \App::make(UserService::class);

        $nearbyPeople = $userService->getNearbyUsers($user);

        // fail early if there is nobody nearby
        if (count($nearbyPeople) == 0) {
            return [];
        }
        return $this->postRepository->getForUsers($nearbyPeople, $filter);

    }

} 