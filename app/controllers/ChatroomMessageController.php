<?php

use Giraffe\Chat\ChatMessageTransformer;
use Giraffe\Chat\ChatMessagingService;
use Giraffe\Chat\ChatService;
use Giraffe\Common\Controller;

class ChatroomMessageController extends Controller
{
    /**
     * @var ChatService
     */
    private $chatService;
    /**
     * @var ChatMessagingService
     */
    private $chatMessagingService;

    public function __construct(ChatService $chatService, ChatMessagingService $chatMessagingService)
    {
        parent::__construct();
        $this->chatService = $chatService;
        $this->chatMessagingService = $chatMessagingService;
    }

    public function add($room)
    {
        $message = $this->chatMessagingService->sendMessage($room, $this->gatekeeper->me(), Input::get('message'));
        return $this->withItem($message, new ChatMessageTransformer(), 'messages');
    }

    public function recent($room)
    {
        $messages = $this->chatMessagingService->getRecentMessages($room);
        return $this->withCollection($messages, new ChatMessageTransformer(), 'messages');
    }
} 