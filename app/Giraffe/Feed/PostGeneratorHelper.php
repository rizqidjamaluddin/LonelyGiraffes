<?php  namespace Giraffe\Feed;

use Giraffe\Feed\Postable;
use Illuminate\Support\Str;

class PostGeneratorHelper
{

    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
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
                'hash' => Str::random(32)
            ]
        );
        $post = $this->postRepository->getByHashWithPostable($post->hash);
        return $post;
    }
} 