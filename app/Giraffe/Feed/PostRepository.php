<?php  namespace Giraffe\Feed;

use Giraffe\Common\EloquentRepository;
use Giraffe\Feed\Postable;
use Giraffe\Feed\PostModel;

class PostRepository extends EloquentRepository
{

    public function __construct(PostModel $postModel)
    {
        parent::__construct($postModel);
    }

    public function getByHashWithPostable($hash)
    {
        return $this->model->with('postable', 'postable.author')->where('hash', $hash)->first();
    }

    public function getForUser($userId)
    {
        return $this->model->with('author', 'postable', 'postable.author')
            ->take(10)
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getGlobal()
    {
        return $this->model
            ->with('postable', 'postable.author')
            ->take(10)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getGlobalAfterId($cursor)
    {
        return $this->model
            ->with('author', 'postable', 'postable.author')
            ->take(10)
            ->where('id', '>', $cursor)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getGlobalBeforeId($cursor)
    {
        return $this->model
            ->with('author', 'postable', 'postable.author')
            ->take(10)
            ->where('id', '<', $cursor)
            ->orderBy('id', 'desc')
            ->get();
    }

} 