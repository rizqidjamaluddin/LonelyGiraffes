<?php  namespace Giraffe\Chat;

use Giraffe\Common\Hash;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\Service;
use Giraffe\Parser\Parser;
use Str;

class ChatMessagingService extends Service
{

    /**
     * @var ChatroomRepository
     */
    private $chatroomRepository;
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        ChatroomRepository $chatroomRepository,
        ChatMessageRepository $chatMessageRepository,
        Parser $parser
    ) {
        $this->chatroomRepository = $chatroomRepository;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->parser = $parser;
        parent::__construct();
    }

    public function sendMessage($room, $user, $message)
    {
        $room = $this->chatroomRepository->getByHash($room);

        $this->gatekeeper->mayI('chat', $room)->please();

        // this may break formatting, so clients should help enforce this
        $message = Str::limit($message, 280, '');
        if (strlen($message) < 1) {
            throw new InvalidCreationException;
        }

        $attributes = [];
        $attributes['chatroom_id'] = $room->id;
        $attributes['user_id'] = $user->id;
        $attributes['body'] = $message;
        $attributes['html_body'] = $this->parser->parseComment($message);
        $attributes['hash'] = new Hash();

        $generated = $this->chatMessageRepository->create($attributes);

        return $generated;
    }

    public function getRecentMessages($room)
    {
        $room = $this->chatroomRepository->getByHash($room);
        $recent = $this->chatMessageRepository->getRecentForRoom($room);
        return $recent;
    }
} 