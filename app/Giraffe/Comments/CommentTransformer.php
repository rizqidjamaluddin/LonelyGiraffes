<?php  namespace Giraffe\Comments; 
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    public function transform(CommentModel $commentModel)
    {
        return $commentModel->toArray();
    }
} 