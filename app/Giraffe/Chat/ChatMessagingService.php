<?php  namespace Giraffe\Chat;

use Giraffe\Common\Value\Hash;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\Service;
use Giraffe\Parser\Parser;
use Giraffe\Users\UserRepository;
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
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ChatroomMembershipRepository
     */
    private $chatroomMembershipRepository;

    public function __construct(
        ChatroomRepository $chatroomRepository,
        ChatMessageRepository $chatMessageRepository,
        ChatroomMembershipRepository $chatroomMembershipRepository,
        UserRepository $userRepository,
        Parser $parser
    ) {
        $this->chatroomRepository = $chatroomRepository;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatroomMembershipRepository = $chatroomMembershipRepository;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        parent::__construct();
    }

    public function sendMessage($room, $user, $message)
    {
        $room = $this->chatroomRepository->getByHash($room);

        $this->gatekeeper->mayI('chat', $room)->please();

        // this may break formatting, so clients should help enforce this
        $message = Str::limit($message, 250, '');
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

    public function getRecentMessages($room, $user, $options)
    {
        $user = $this->userRepository->getByHash($user);
        $room = $this->chatroomRepository->getByHash($room);

        $this->gatekeeper->mayI('read', $room)->please();

        $userMembership = $this->chatroomMembershipRepository->findForUserInRoom($user, $room);

        $earliestLimit = $userMembership->created_at;
        $options = array_only($options, ['before', 'after', 'take']);
        $options['earliest'] = $earliestLimit;

        $options = $this->translateHashOptionsToIDs($options);
        $options = $this->truncateTakeOption($options);

        $recent = $this->chatMessageRepository->getRecentIn($room, $options);
        return $recent;
    }

    protected function translateHashOptionsToIDs($options)
    {
        if ($before = array_get($options, 'before')) {
            $options['before'] = $this->chatMessageRepository->getByHash($before)->id;
        }
        if ($after = array_get($options, 'after')) {
            $options['after'] = $this->chatMessageRepository->getByHash($after)->id;
            return $options;
        }
        return $options;
    }

    protected function truncateTakeOption($options)
    {
        if (array_get($options, 'take') > 50) {
            $options['take'] = 50;
            return $options;
        }
        return $options;
    }
} 