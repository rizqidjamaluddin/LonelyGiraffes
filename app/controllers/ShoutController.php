<?php

use Giraffe\Shouts\ShoutService;

class ShoutController extends BaseController
{
    /**
     * @var Giraffe\Shouts\ShoutService
     */
    private $shoutService;

    public function __construct(ShoutService $shoutService)
    {
        $this->shoutService = $shoutService;
    }

    public function store()
    {
        $body = Input::get('body');
        $post = $this->shoutService->createShout(Auth::user(), $body);
        return $post;
    }

    public function show($hash)
    {
        return $this->shoutService->getShout($hash);
    }

    public function destroy($hash)
    {
        return $this->shoutService->deleteShout($hash);
    }

    public function showAll($hash)
    {
        return $this->shoutService->getShouts($hash);
    }
} 