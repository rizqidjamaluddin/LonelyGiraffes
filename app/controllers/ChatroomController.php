<?php

use Giraffe\Chat\ChatroomTransformer;
use Giraffe\Common\Controller;
use Giraffe\Chat\ChatService;


class ChatroomController extends Controller
{
    /**
     * @var \Giraffe\Chat\ChatService
     */

    private $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
        parent::__construct();
    }

    public function create()
    {
        $create = $this->chatService->createChatroom($this->gatekeeper->me());
        return $this->withItem($create, new ChatroomTransformer(), 'chatrooms');
    }

    public function show($chatroom)
    {
        $chatroom = $this->chatService->getChatroom($chatroom);
        return $this->withItem($chatroom, new ChatroomTransformer(), 'chatrooms');

    }



}