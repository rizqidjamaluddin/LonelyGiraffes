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
        $amount = array_get($options, 'take') ?: 30;

        $q = $this->model;
        $q = $this->appendSinceOption($q, $options);
        $q = $this->appendOptions($q, $options);

        return $q->where('chatroom_id', $room->id)->limit($amount)->orderBy('id', 'desc')->get();
    }

    protected function appendSinceOption($q, $options)
    {
        $since = array_get($options, 'earliest');
        if (!$since) return $q;
        if ($since instanceof Carbon) $since = $since->toDateTimeString();
        return $q->where('created_at', '>=', $since);
    }

    protected function appendOptions($query, $options)
    {
        $take = array_get($options, 'take') ?: 10;
        $query = $query->take($take);
        if ($after = array_get($options, 'after')) {
            $query = $query->where('id', '>', $after);
        }
        if ($before = array_get($options, 'before')) {
            $query = $query->where('id', '<', $before);
        }
        return $query;
    }
} 