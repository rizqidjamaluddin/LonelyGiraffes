<?php

use Giraffe\Common\Controller;
use Giraffe\Shouts\ShoutService;
use Giraffe\Shouts\ShoutTransformer;

class ShoutController extends Controller
{
    /**
     * @var Giraffe\Shouts\ShoutService
     */
    private $shoutService;

    public function __construct(ShoutService $shoutService)
    {
        $this->shoutService = $shoutService;
        parent::__construct();
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