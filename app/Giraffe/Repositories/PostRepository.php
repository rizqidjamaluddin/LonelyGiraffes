<?php  namespace Giraffe\Repositories; 

use Giraffe\Contracts\Postable;
use Giraffe\Models\PostModel;

class PostRepository extends BaseEloquentRepository
{

    /**
     * @var \Giraffe\Models\PostModel
     */
    private $postModel;

    public function __construct(PostModel $postModel)
    {
        $this->postModel = $postModel;
    }

} 