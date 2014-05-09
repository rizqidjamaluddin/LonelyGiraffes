<?php  namespace Giraffe\Services;

use Eloquent;
use Giraffe\Helpers\Parser\Parser;
use Giraffe\Helpers\Rights\Gatekeeper;
use Giraffe\Models\PostModel;
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

    public function __construct(
        Gatekeeper $gatekeeper,
        Parser $parser,
        PostRepository $postRepository,
        UserRepository $userRepository,
        ShoutRepository $shoutRepository
        )
    {
        $this->gatekeeper = $gatekeeper;
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;
        $this->parser = $parser;
    }

    public function addCommentOnPost($user, $post, $comment)
    {

    }

    /**
     * Create a new post.
     *
     * Expected metadata:
     * type: Type of post, defaults to shout if not set or unknown
     *
     * @param        $user
     * @param string $post
     * @param array  $metadata
     *
     * @return PostModel
     */
    public function createPost($user, $post, array $metadata)
    {
        $this->gatekeeper->mayI('create', 'post');

        $user = $this->userRepository->get($user);
        $type = array_key_exists('type', $metadata) ? $metadata['type'] : null;
        switch ($type) {
            default: {
                $htmlBody = $this->parser->parseComment($post);
                $postable = $this->shoutRepository->create([
                        'user_id'   => $user->id,
                        'body'      => $post,
                        'html_body' => $htmlBody
                    ]);
                break;
            }
        }

        $post = $this->postRepository->createWithPostable(['user_id' => $user->id], $postable);
        return $post;
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