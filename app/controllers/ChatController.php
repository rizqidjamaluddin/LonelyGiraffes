<?php

use Giraffe\Common\Controller;
use Giraffe\Chat\ChatService;


class ChatContoller extends Controller
{
    /**
     * @var \Giraffe\Chat\ChatService
     */

    private $chatService;

    public function __construct(EventService $chatService)
    {
        $this->chatService = $chatService;
        parent::__construct();
    }



}