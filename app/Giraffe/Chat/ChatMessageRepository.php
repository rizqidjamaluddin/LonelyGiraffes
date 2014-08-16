<?php  namespace Giraffe\Chat;

use Carbon\Carbon;
use Giraffe\Common\EloquentRepository;
use Giraffe\Users\UserModel;

class ChatMessageRepository extends EloquentRepository
{
    public function __construct(ChatMessageModel $chatMessageModel)
    {
        parent::__construct($chatMessageModel);
    }

    public function getRecentIn(ChatroomModel $room, $options)
    {
        $q = $this->model;
        $q = $this->appendSinceOption($q, $options);

        return $q->where('chatroom_id', $room->id)->limit(30)->orderBy('id', 'desc')->get();
    }

    protected function appendSinceOption($q, $options)
    {
        $since = array_get($options, 'earliest');
        if (!$since) return $q;
        if ($since instanceof Carbon) $since = $since->toDateTimeString();
        return $q->where('created_at', '>=', $since);
    }
} 