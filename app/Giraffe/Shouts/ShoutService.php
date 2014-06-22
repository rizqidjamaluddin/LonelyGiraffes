<?php  namespace Giraffe\Shouts;

use Giraffe\Authorization\Gatekeeper;
use Giraffe\Common\Service;
use Giraffe\Feed\PostGeneratorHelper;
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

    public function __construct(
        UserRepository $userRepository,
        ShoutRepository $shoutRepository,
        Parser $parser,
        PostGeneratorHelper $postGeneratorHelper
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->shoutRepository = $shoutRepository;

        $this->parser = $parser;
        $this->postGeneratorHelper = $postGeneratorHelper;
    }

    public function createShout($user, $body)
    {
        $parsed = $this->parser->parseComment($body);
        $hash = Str::random(32);
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
        return $post;
    }

    public function getShout($hash)
    {
        return $this->shoutRepository->getByHash($hash);
    }

    public function getShouts($userHash)
    {
        $user = $this->userRepository->getByHash($userHash);
        return $this->shoutRepository->getAllShoutsForUser($user->id);
    }

    public function deleteShout($hash)
    {
        return $this->shoutRepository->deleteByHash($hash);
    }
} 