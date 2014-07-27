<?php  namespace Giraffe\Chat;

use Giraffe\Common\Service;
use Giraffe\Chat\ChatRepository;
use Giraffe\Users\UserRepository;

class ChatService 
{

    /**
     * @var ChatroomRepository
     */
    private $chatroomRepository;

    public function __construct(ChatroomRepository $chatroomRepository)
    {
        $this->chatroomRepository = $chatroomRepository;
    }

    public function createChatroom($owner)
    {

        $create = $this->chatroomRepository->create([]);
    }

    public function showChatroom($conversation)
    {

    }

    public function acceptConversationInvite($user, $conversation)
    {

    }

    public function denyConversationInvite($user, $conversation)
    {

    }

    public function hideConversation($conversation)
    {

    }

    public function leaveConversaton()
    {

    }

    public function listUserConversations()
    {

    }

    public function sendConversationInvite()
    {

    }

    public function sendMessage()
    {

    }
} 