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
        $post = $this->shoutService->create(Auth::user(), $body);
        return $post;
    }
} 