<?php  namespace Giraffe\Feed;

use Giraffe\Common\EloquentRepository;
use Giraffe\Contracts\Postable;
use Giraffe\Feed\PostModel;

class PostRepository extends EloquentRepository
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