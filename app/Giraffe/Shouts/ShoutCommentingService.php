<?php  namespace Giraffe\Shouts;

use Giraffe\Comments\CommentStreamModel;
use Giraffe\Comments\CommentStreamRepository;
use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Collection;

class ShoutCommentingService extends Service
{
    /**
     * @var ShoutRepository
     */
    private $shoutRepository;
    /**
     * @var \Giraffe\Comments\CommentStreamRepository
     */
    private $commentStreamRepository;
    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;

    public function __construct(
        ShoutRepository $shoutRepository,
        CommentStreamRepository $commentStreamRepository,
        UserRepository $userRepository
    ) {
        $this->shoutRepository = $shoutRepository;
        $this->commentStreamRepository = $commentStreamRepository;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function getForShout($shout)
    {
        $shout = $this->shoutRepository->getByHash($shout);

        return new Collection;
    }

    public function addComment($shout, $body, $user)
    {
        /** @var ShoutModel $shout */
        $shout = $this->shoutRepository->getByHash($shout);
        $user = $this->userRepository->getByHash($user);

        /** @var CommentStreamModel $stream */
        $stream = $this->commentStreamRepository->getOrCreateFor($shout);
        $comment = $stream->postComment($body, $user);
        return $comment;
    }
} 