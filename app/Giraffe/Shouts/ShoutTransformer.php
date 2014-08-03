<?php  namespace Giraffe\Shouts; 

use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ShoutTransformer extends TransformerAbstract
{

    public function transform(ShoutModel $shoutModel)
    {
        $author = (new UserTransformer())->transform($shoutModel->fetchAuthor());

        return [
            'hash' => $shoutModel->hash,
            'body' => $shoutModel->body,
            'html_body' => $shoutModel->html_body,
            'timestamp' => (string) $shoutModel->created_at,
            'links' =>[
                'author' => $author
            ]
        ];
    }

} 