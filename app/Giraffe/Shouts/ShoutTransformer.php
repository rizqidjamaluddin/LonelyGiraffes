<?php  namespace Giraffe\Shouts; 

use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ShoutTransformer extends TransformerAbstract
{

    public function transform(ShoutModel $shoutModel)
    {
        /** @var UserTransformer $userTransformer */
        $userTransformer = \App::make(UserTransformer::class);

        $commentatorList = [];
        $commentators = $shoutModel->getCommentators();
        foreach ($commentators as $commentator) {
            $commentatorList[] = $userTransformer->transform($commentator);
        }

        return [
            'hash' => $shoutModel->hash,
            'body' => $shoutModel->body,
            'html_body' => $shoutModel->html_body,
            'timestamp' => (string) $shoutModel->created_at,
            'comment_count' => $shoutModel->getCommentCount(),
            'commentators' => $commentatorList,
            'links' =>[
                'author' => $userTransformer->transform($shoutModel->fetchAuthor())
            ]
        ];
    }

} 