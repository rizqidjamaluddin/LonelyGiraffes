<?php  namespace Giraffe\Comments; 
use Giraffe\Common\EloquentRepository;
use Giraffe\Users\UserRepository;

class CommentRepository extends EloquentRepository
{

    public function __construct(CommentModel $commentModel)
    {
        parent::__construct($commentModel);
    }

    public function getFor(CommentStreamModel $stream, $options = [])
    {
        $q = $this->model;
        $q = $this->appendOptions($q, $options);

        return $q->where('comment_stream_id', $stream->id)->orderBy('id', 'desc')->get();
    }

    public function countFor(CommentStreamModel $stream)
    {
        return $this->model->where('comment_stream_id', $stream->id)->count();
    }

    public function getUsersFor(CommentStreamModel $stream)
    {
        $users = $this->model->where('comment_stream_id', $stream->id)->lists('user_id');

        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        return $userRepository->getMany($users);
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