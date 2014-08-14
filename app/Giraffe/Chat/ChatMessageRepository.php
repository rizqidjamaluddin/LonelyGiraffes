<?php  namespace Giraffe\Chat; 
use Giraffe\Common\EloquentRepository;

class ChatMessageRepository extends EloquentRepository
{
    public function __construct(ChatMessageModel $chatMessageModel)
    {
        parent::__construct($chatMessageModel);
    }

    public function getRecentForRoom(ChatroomModel $room)
    {
        return $this->model->where('chatroom_id', $room->id)->limit(30)->orderBy('created_at', 'desc')->get();
    }
} 