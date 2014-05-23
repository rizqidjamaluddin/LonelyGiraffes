<?php  namespace Giraffe\Feed;

use Giraffe\Common\BaseEloquentRepository;
use Giraffe\Contracts\Postable;
use Giraffe\Feed\PostModel;

class PostRepository extends BaseEloquentRepository
{

    /**
     * @var \Giraffe\Feed\PostModel
     */
    private $postModel;

    public function __construct(PostModel $postModel)
    {
        $this->postModel = $postModel;
    }

} 