<?php

use Giraffe\Chat\ChatroomTransformer;
use Giraffe\Common\Controller;
use Giraffe\Chat\ChatService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


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

    public function index()
    {
        $options = Input::only([]);

        if (Input::exists('participating')) {
            $results = $this->chatService->findChatroomsContainingUser($this->gatekeeper->me(), $options);
        } else {
            throw new BadRequestHttpException;
        }

        return $this->withCollection($results, new ChatroomTransformer(), 'chatrooms');
    }

    public function edit($room)
    {
        if (Input::exists('title')) {
            $chatroom = $this->chatService->updateRoomTitle($room, Input::get('title'));
            return $this->withItem($chatroom, new ChatroomTransformer(), 'chatrooms');
        }

        // just return the same thing if no changes were made
        return $this->show($room);
    }

    public function add($room)
    {
        $this->chatService->addUserToRoom($room, Input::get('user'));
        return ['message' => 'User added'];
    }

    public function leave($room)
    {
        $this->chatService->removeUserFromRoomVoluntarily($room, $this->gatekeeper->me());
        return ['message' => 'Room left'];
    }

    public function kick($room)
    {
        $user = $this->chatService->kickUserFromRoom($room, Input::get('user'));
        return ['message' => $user];
    }
    

}