<?php  namespace Giraffe\Comments; 
use Giraffe\Common\EloquentRepository;

class CommentRepository extends EloquentRepository
{
    /**
     * @var CommentModel
     */
    private $commentModel;

    public function __construct(CommentModel $commentModel)
    {
        $this->commentModel = $commentModel;
    }
} 