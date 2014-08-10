<?php  namespace Giraffe\Chat;

use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

class ChatroomTransformer extends TransformerAbstract
{
    public function transform(ChatroomModel $model)
    {

        $participantModels = $model->participants();
        $membershipTransformer = \App::make(ChatroomMembershipTransformer::class);

        $participants = new Collection;
        foreach ($participantModels as $k) {
            $participants->push($membershipTransformer->transform($k));
        }

        return [
            'hash'         => $model->hash,
            'href'         => $this->buildUrl($model->hash),
            'participants' => $participants,
            'participantCount' => count($participants)
        ];
    }

    protected function buildUrl($hash)
    {
        return url('api/chatrooms/' . $hash);
    }
} 