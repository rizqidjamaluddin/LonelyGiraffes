<?php  namespace Giraffe\Chat;

use Carbon\Carbon;
use Giraffe\Common\EloquentRepository;
use Giraffe\Common\Internal\QueryFilter;
use Giraffe\Users\UserModel;

class ChatMessageRepository extends EloquentRepository
{
    public function __construct(ChatMessageModel $chatMessageModel)
    {
        parent::__construct($chatMessageModel);
    }

    public function getRecentIn(ChatroomModel $room, QueryFilter $options)
    {
        $amount = $options->get('take');

        $q = $this->model;
        $q = $this->appendSinceOption($q, $options);
        $q = $this->appendOptions($q, $options);

        return $q->where('chatroom_id', $room->id)->limit($amount)->orderBy('id', 'desc')->get();
    }

    protected function appendSinceOption($q, QueryFilter $options)
    {
        $since = $options->get('earliest');
        if (!$since) return $q;
        if ($since instanceof Carbon) $since = $since->toDateTimeString();
        return $q->where('created_at', '>=', $since);
    }

    protected function appendOptions($query, QueryFilter $options)
    {
        $query = $query->take($options->get('take'));
        if ($after = $options->get('after')) {
            $query = $query->where('id', '>', $after->id);
        }
        if ($before = $options->get('before')) {
            $query = $query->where('id', '<', $before->id);
        }
        return $query;
    }
} 