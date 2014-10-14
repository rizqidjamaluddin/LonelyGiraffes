<?php  namespace Giraffe\Chat; 

use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class ChatroomMembershipTransformer extends Transformer
{
    /**
     * @param ChatroomMembershipModel $model
     * @return array
     */
    public function transform($model)
    {
        $userTransformer = \App::make(UserTransformer::class);

        return [
            'joined' => (string) $model->created_at,
            'flag' => $model->flag,
            'user' => $userTransformer->transform($model->user)
        ];
    }
} 