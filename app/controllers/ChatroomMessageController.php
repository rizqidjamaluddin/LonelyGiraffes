<?php

use Giraffe\Chat\ChatMessageRepository;
use Giraffe\Chat\ChatMessageTransformer;
use Giraffe\Chat\ChatMessagingService;
use Giraffe\Chat\ChatService;
use Giraffe\Common\Controller;
use Giraffe\Common\Internal\QueryFilter;

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
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;

    public function __construct(ChatService $chatService,
                                ChatMessagingService $chatMessagingService,
                                ChatMessageRepository $chatMessageRepository)
    {
        parent::__construct();
        $this->chatService = $chatService;
        $this->chatMessagingService = $chatMessagingService;
        $this->chatMessageRepository = $chatMessageRepository;
    }

    public function add($room)
    {
        $message = $this->chatMessagingService->sendMessage($room, $this->gatekeeper->me(), Input::get('message'));
        return $this->withItem($message, new ChatMessageTransformer(), 'messages');
    }

    public function recent($room)
    {
        $options = new QueryFilter();
        $options->set('before', Input::get('before'), null, $this->chatMessageRepository);
        $options->set('after', Input::get('after'), null, $this->chatMessageRepository);
        $options->set('take', Input::get('take'), 30, null, [1,50]);

        $messages = $this->chatMessagingService->getRecentMessages($room, $this->gatekeeper->me(), $options);
        return $this->withCollection($messages, new ChatMessageTransformer(), 'messages');
    }
} 