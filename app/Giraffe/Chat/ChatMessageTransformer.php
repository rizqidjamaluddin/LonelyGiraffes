<?php  namespace Giraffe\Chat; 
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ChatMessageTransformer extends Transformer
{
    /**
     * @param ChatMessageModel $message
     * @return array
     */
    public function transform($message)
    {
        $user = (new UserTransformer())->transform($message->author());

        return [
            'body' => $message->body,
            'html_body' => $message->html_body,
            'hash' => $message->hash,
            'author' => $user,
        ];
    }
} 