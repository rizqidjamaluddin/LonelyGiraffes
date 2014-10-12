<?php  namespace Giraffe\Feed; 

use Dingo\Api\Transformer\TransformableInterface;
use Giraffe\Support\Transformer\Transformable;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform(PostModel $post)
    {
        $author = $post->fetchAuthor();

        if ($author instanceof TransformableInterface || $author instanceof Transformable) {
            $transformer = $author->getTransformer();
            $author = $transformer->transform($author);
        };

        $body = $post->postable;

        if ($body instanceof TransformableInterface || $body instanceof Transformable) {
            $transformer = $body->getTransformer();
            $child = $transformer->transform($body);
        } else {
            if ($body) {
                $child = (string) $body;
            } else {
                $child = [];
            }
        };

        if (!$body) {
            $type = 'deleted';
        } else {
            $type = $body->getType();
        }

        return [
            'hash' => $post->hash,
            'type' => $type,
            'body' => $child,
            'href' => url('api/posts/'.$post->hash),
            'links' => [
                'author' => $author,
            ],
        ];
    }
} 