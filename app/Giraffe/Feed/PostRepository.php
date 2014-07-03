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

    public function getGlobal($page)
    {
        return $this->model->with('postable', 'postable.author')->take(10)->skip($page * 10)->get();
    }

} 