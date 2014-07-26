<?php  namespace Giraffe\Feed; 

use Dingo\Api\Transformer\TransformableInterface;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform(PostModel $post)
    {
        $author = $post->fetchAuthor();

        if ($author instanceof TransformableInterface) {
            $transformer = $author->getTransformer();
            $author = $transformer->transform($author);
        };

        $body = $post->postable;

        if ($body instanceof TransformableInterface) {
            $transformer = $body->getTransformer();
            $body = $transformer->transform($body);
        };

        return [
            'hash' => $post->hash,
            'body' => $body,
            'href' => url('api/posts/'.$post->hash),
            'links' => [
                'author' => $author,
            ],
        ];
    }
} 