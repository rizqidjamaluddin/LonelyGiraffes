<?php  namespace Giraffe\Chat;

use Giraffe\Common\Hash;
use Giraffe\Common\Service;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Support\Collection;
use Str;

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
        ChatroomMembershipRepository $chatroomMembershipRepository,
        ChatMessageRepository $chatMessageRepository,
        UserRepository $userRepository,
        Parser $parser
    ) {
        $this->chatroomRepository = $chatroomRepository;
        $this->chatroomMembershipRepository = $chatroomMembershipRepository;
        $this->userRepository = $userRepository;

        parent::__construct();
        $this->chatMessageRepository = $chatMessageRepository;
        $this->parser = $parser;
    }

    public function createChatroom($owner)
    {
        $this->gatekeeper->mayI('create', 'chatroom')->please();
        $owner = $this->userRepository->getByHash($owner);
        $create = $this->chatroomRepository->create(['hash' => new Hash]);

        // add author automatically to new chatroom
        $membership = $this->chatroomMembershipRepository->create(
            [
                'user_id'         => $owner->id,
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
            if ($this->gatekeeper->mayI('read', $room)->canI()) {
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

    public function addUserToRoom($room, $target)
    {
        /** @var ChatroomModel $room */
        $room = $this->chatroomRepository->getByHash($room);

        /** @var UserModel $target */
        $target = $this->userRepository->getByHash($target);

        // finish if user is already in room
        if ($this->chatroomMembershipRepository->findForUserInRoom($target, $room)) {
            return true;
        }

        // create new participant
        $membership = $this->chatroomMembershipRepository->create(
            [
                'user_id'         => $target->id,
                'conversation_id' => $room->id
            ]
        );

        return true;
    }

    public function updateRoomTitle($room, $title)
    {
        $room = $this->chatroomRepository->getByHash($room);
        $this->gatekeeper->mayI('update', $room)->please();

        $room->name = $title;
        $this->chatroomRepository->save($room);

        return $room;
    }

    public function removeUserFromRoomVoluntarily($room, $me)
    {
        $user = $this->userRepository->getByHash($me);
        $room = $this->chatroomRepository->getByHash($room);

        $membership = $this->chatroomMembershipRepository->findForUserInRoom($user, $room);

        $this->gatekeeper->mayI('delete', $membership)->please();

        $this->chatroomMembershipRepository->delete($membership);
    }
} 