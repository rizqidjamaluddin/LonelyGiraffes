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
        return $this->model->with('postable')->where('hash', $hash)->first();
    }

} 