<?php  namespace Giraffe\Feed; 

use Dingo\Api\Transformer\TransformableInterface;
use Giraffe\Support\Transformer\DefaultTransformable;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class PostTransformer extends Transformer
{
    /**
     * @param PostModel $post
     * @return array
     */
    public function transform($post)
    {
        $userTransformer = new UserTransformer();
        $author = $userTransformer->transform($post->fetchAuthor());

        $body = $post->postable;

        if ($body instanceof DefaultTransformable) {
            $transformer = $body->getDefaultTransformer();
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