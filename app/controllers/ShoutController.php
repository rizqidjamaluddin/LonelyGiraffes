<?php

use Giraffe\Common\Controller;
use Giraffe\Shouts\ShoutService;
use Giraffe\Shouts\ShoutTransformer;
use Giraffe\Users\UserRepository;

class ShoutController extends Controller
{
    /**
     * @var Giraffe\Shouts\ShoutService
     */
    private $shoutService;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(ShoutService $shoutService, UserRepository $userRepository)
    {
        $this->shoutService = $shoutService;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function index()
    {
        // serve ?user=$hash
        if (Input::exists('user')) {
            $shouts = $this->shoutService->getShouts(Input::get('user'));
            return $this->withCollection($shouts, new ShoutTransformer(), 'shouts');
        }

        throw new HttpRequestMethodException;
    }

    public function store()
    {
        $body = Input::get('body');
        $shout = $this->shoutService->createShout($this->gatekeeper->me(), $body);
        return $this->withItem($shout, new ShoutTransformer(), 'shouts');
    }

    public function show($hash)
    {
        $shout = $this->shoutService->getShout($hash);
        return $this->withItem($shout, new ShoutTransformer(), 'shouts');
    }

    public function destroy($hash)
    {
        $delete = $this->shoutService->deleteShout($hash);
        return ['message' => 'Shout deleted'];
    }

    public function showAll($userHash)
    {
        $shouts = $this->shoutService->getShouts($userHash);
        return $this->withCollection($shouts, new ShoutTransformer(), 'shouts');
    }
} 