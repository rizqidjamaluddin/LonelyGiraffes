<?php  namespace Giraffe\Feed;

use Cache;
use Giraffe\Common\Service;
use Giraffe\Geolocation\LocationHelper;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserRepository;
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
     * @var \Giraffe\Geolocation\LocationHelper
     */
    private $locationHelper;
    /**
     * @var \Illuminate\Cache\Repository
     */
    private $cache;

    public function __construct(
        Parser $parser,
        LocationHelper $locationHelper,
        PostRepository $postRepository,
        UserRepository $userRepository,
        Repository $cache
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        $this->locationHelper = $locationHelper;
        $this->postRepository = $postRepository;
        $this->cache = $cache;
    }

    public function getGlobalFeed($options)
    {
        $this->gatekeeper->mayI('read', 'feed')->please();
        $options = array_only($options, ['before', 'after', 'take']);

        $options = $this->translateHashOptionsToIDs($options);

        if (count($options) > 0) {
            return $this->postRepository->getGlobal($options);
        }

        // only update the top post cache if it was called without options
        $fetch = $this->postRepository->getGlobal();
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

    public function getUserPosts($user, $options)
    {
        $user = $this->userRepository->getByHash($user);
        $options = $this->translateHashOptionsToIDs($options);
        // no specific gatekeeper check here; adjust PostRepository call if policy changes
        $this->gatekeeper->mayI('read', 'posts')->please();
        return $this->postRepository->getForUser($user->id, $options);
    }

    /**
     * @param $options
     * @return mixed
     */
    protected function translateHashOptionsToIDs($options)
    {
        if ($before = array_get($options, 'before')) {
            $options['before'] = $this->postRepository->getByHash($before)->id;
        }
        if ($after = array_get($options, 'after')) {
            $options['after'] = $this->postRepository->getByHash($after)->id;
            return $options;
        }
        return $options;
    }


} 