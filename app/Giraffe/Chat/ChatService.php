<?php  namespace Giraffe\Chat;

use Giraffe\Common\Hash;
use Giraffe\Common\Service;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Collection;

class ChatService extends Service
{

    /**
     * @var ChatroomRepository
     */
    private $chatroomRepository;
    /**
     * @var ChatroomMembershipRepository
     */
    private $chatroomMembershipRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        ChatroomRepository $chatroomRepository,
        ChatroomMembershipRepository $chatroomMembershipRepository,
        UserRepository $userRepository
    ) {
        $this->chatroomRepository = $chatroomRepository;
        $this->chatroomMembershipRepository = $chatroomMembershipRepository;
        $this->userRepository = $userRepository;
        
        parent::__construct();
    }

    public function createChatroom($owner)
    {
        $this->gatekeeper->mayI('create', 'chatroom')->please();
        $owner = $this->userRepository->getByHash($owner);
        $create = $this->chatroomRepository->create(['hash' => new Hash]);

        // add author automatically to new chatroom
        $membership = $this->chatroomMembershipRepository->create(
            [
                'user_id' => $owner->id,
                'conversation_id' => $create->id
            ]
        );

        return $create;
    }

    public function findChatroomsContainingUser($user, $options)
    {
        $user = $this->userRepository->getByHash($user);
        $memberships = $this->chatroomMembershipRepository->findForUser($user);

        // bail early if there are no chatroom memberships
        if (!$memberships) {
            return new Collection;
        }

        $chatrooms = new Collection;
        foreach ($memberships as $membership) {
            $room = $this->chatroomRepository->findForMembership($membership);

            // sanity check to make sure we can read from the room before adding it
            if (!$this->gatekeeper->mayI('read', $room)->canI()) {
                $chatrooms->push($room);
            }
        }

        return $chatrooms;

    }

    public function getChatroom($roomHash)
    {
        $room = $this->chatroomRepository->getByHash($roomHash);
        $this->gatekeeper->mayI('read', $room)->please();
        return $room;
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

    public function leaveConversation()
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