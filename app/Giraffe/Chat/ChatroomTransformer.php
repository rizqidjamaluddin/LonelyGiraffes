<?php  namespace Giraffe\Chat;

use Giraffe\Support\Transformer\Transformer;
use Illuminate\Support\Collection;

class ChatroomTransformer extends Transformer
{
    /**
     * @param ChatroomModel $model
     * @return array
     */
    public function transform($model)
    {

        $participantModels = $model->participants();
        $membershipTransformer = \App::make(ChatroomMembershipTransformer::class);

        $participants = new Collection;
        foreach ($participantModels as $k) {
            $participants->push($membershipTransformer->transform($k));
        }

        return [
            'name'             => $model->name ?: false,
            'hash'             => $model->hash,
            'href'             => $this->buildUrl($model->hash),
            'title'            => $model->name ?: false,
            'participants'     => $participants,
            'participantCount' => count($participants)
        ];
    }

    protected function buildUrl($hash)
    {
        return url('api/chatrooms/' . $hash);
    }
} 