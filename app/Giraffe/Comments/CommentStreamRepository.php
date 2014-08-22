<?php  namespace Giraffe\Comments; 
use Giraffe\Common\EloquentRepository;

class CommentStreamRepository extends EloquentRepository
{
    public function __construct(CommentStreamModel $commentStreamModel)
    {
        parent::__construct($commentStreamModel);
    }

    /**
     * Fetch, or create a blank, comment stream for a commentable model.
     *
     * @param Commentable $commentable
     * @return CommentStreamModel
     */
    public function getOrCreateFor(Commentable $commentable)
    {
        $class = get_class($commentable);
        $id = $commentable->getKey();
        $find = $this->model->where('commentable_type', $class)->where('commentable_id', $id)->first();

        // return stream if immediately available
        if ($find) return $find;

        // create new stream if necessary
        $create = CommentStreamModel::generate($commentable);
        $this->save($create);
        return $create;
    }
} 