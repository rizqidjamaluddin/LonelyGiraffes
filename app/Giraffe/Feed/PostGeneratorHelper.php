<?php  namespace Giraffe\Feed;

use Giraffe\Common\Value\Hash;
use Giraffe\Feed\Postable;
use Giraffe\Sockets\Pipeline;
use Illuminate\Support\Str;

class PostGeneratorHelper
{

    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var FeedService
     */
    private $feedService;

    public function __construct(PostRepository $postRepository, FeedService $feedService)
    {
        $this->postRepository = $postRepository;
        $this->feedService = $feedService;
    }

    /**
     * Makes a post parent for any given postable class.
     *
     * @param Postable $postable
     * @return PostModel
     */
    public function generate(Postable $postable)
    {
        /** @var PostModel $post */
        $post = $this->postRepository->create(
            [
                'postable_type' => get_class($postable),
                'postable_id' => $postable->getId(),
                'user_id' => $postable->getOwnerId(),
                'hash' => new Hash()
            ]
        );
        $this->feedService->invalidateTopPostCache();
        $post = $this->postRepository->getByHashWithPostable($post->hash);

        $this->notify();
        return $post;
    }

    protected function notify()
    {
        /** @var Pipeline $pipeline */
        $pipeline = \App::make(Pipeline::class);
        $pipeline->issue('/posts');
    }
} 