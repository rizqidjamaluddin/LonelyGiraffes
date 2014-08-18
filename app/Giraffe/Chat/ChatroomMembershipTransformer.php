<?php  namespace Giraffe\Chat; 

use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ChatroomMembershipTransformer extends TransformerAbstract
{
    public function transform(ChatroomMembershipModel $model)
    {
        $userTransformer = \App::make(UserTransformer::class);

        return [
            'joined' => (string) $model->created_at,
            'flag' => $model->flag,
            'user' => $userTransformer->transform($model->user)
        ];
    }
} 