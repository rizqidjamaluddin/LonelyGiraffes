<?php  namespace Giraffe\Comments; 
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    public function transform(CommentModel $commentModel)
    {
        /** @var UserTransformer $userTransformer */
        $userTransformer = \App::make(UserTransformer::class);

        return [
            'hash' => $commentModel->hash,
            'body' => $commentModel->body,
            'html_body' => $commentModel->html_body,
            'timestamp' => (string) $commentModel->created_at,
            'links' => [
                'author' => $userTransformer->transform($commentModel->author()),
            ]
        ];
    }
} 