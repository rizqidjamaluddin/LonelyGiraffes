<?php  namespace Giraffe\Chat; 
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ChatMessageTransformer extends TransformerAbstract
{
    public function transform(ChatMessageModel $message)
    {
        $user = (new UserTransformer())->transform($message->author());

        return [
            'body' => $message->body,
            'html_body' => $message->html_body,
            'author' => $user
        ];
    }
} 