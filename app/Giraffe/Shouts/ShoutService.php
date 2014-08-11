<?php  namespace Giraffe\Shouts;

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Common\Service;
use Giraffe\Feed\PostGeneratorHelper;
use Giraffe\Feed\PostRepository;
use Giraffe\Parser\Parser;
use Giraffe\Shouts\ShoutRepository;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Str;

class ShoutService extends Service
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var \Giraffe\Shouts\ShoutRepository
     */
    private $shoutRepository;
    /**
     * @var \Giraffe\Parser\Parser
     */
    private $parser;
    /**
     * @var \Giraffe\Feed\PostGeneratorHelper
     */
    private $postGeneratorHelper;
    /**
     * @var ShoutCreationValidator
     */
    private $creationValidator;
    /**
     * @var \Giraffe\Feed\PostRepository
     */
    private $postRepository;

    public function __construct(
        UserRepository $userRepository,
        ShoutRepository $shoutRepository,
        Parser $parser,
        PostGeneratorHelper $postGeneratorHelper,
        PostRepository $postRepository,
        ShoutCreationValidator $creationValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;

        $this->parser = $parser;
        $this->postGeneratorHelper = $postGeneratorHelper;
        $this->creationValidator = $creationValidator;
        $this->postRepository = $postRepository;
    }

    public function createShout($user, $body)
    {
        $this->gatekeeper->mayI('create', 'shout')->please();

        $body = trim($body);
        $parsed = $this->parser->parseComment($body);
        $hash = Str::random(32);

        $this->creationValidator->validate(['body' => $body]);

        /** @var ShoutModel $shout */
        $shout = $this->shoutRepository->create(
            [
                'user_id' => $user->id,
                'hash' => $hash,
                'body' => $body,
                'html_body' => $parsed
            ]
        );

        $post = $this->postGeneratorHelper->generate($shout);
        return $shout;
    }

    public function getShout($hash)
    {
        $shout = $this->shoutRepository->getByHash($hash);
        $this->gatekeeper->mayI('read', $shout)->please();
        return $shout;
    }

    public function getShouts($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        return $this->shoutRepository->getAllShoutsForUser($user->id);
    }

    public function deleteShout($hash)
    {
        $shout = $this->getShout($hash);
        $this->gatekeeper->mayI('delete', $shout)->please();

        $this->postRepository->deleteForPostable($shout);
        $delete = $this->shoutRepository->deleteByHash($hash);
        return true;
    }
} 