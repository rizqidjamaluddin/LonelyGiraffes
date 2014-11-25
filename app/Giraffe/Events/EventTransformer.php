<?php  namespace Giraffe\Events; 
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserTransformer;

class EventTransformer extends Transformer
{
    /**
     * @param EventModel $model
     * @return array
     */
    public function transform($model)
    {
        $userTransformer = (new UserTransformer());
        $author = $userTransformer->transform($model->owner);

        $commentatorList = [];
        $commentators = $model->getCommentators();
        foreach ($commentators as $commentator) {
            $commentatorList[] = $userTransformer->transform($commentator);
        }

        $participantList = [];
        $participants = $model->getParticipants();
        foreach ($participants as $participant) {
            $participantList[] = $userTransformer->transform($participant);
        }

        return [
            'hash' => $model->hash,
            'name' => $model->name,
            'body' => $model->body,
            'html_body' => $model->html_body,
            'url' => $model->url,
            'location' => $model->location,
            'city' => $model->city,
            'state' => $model->state,
            'country' => $model->country,
            'timestamp' => $model->timestamp,
            'comment_count' => $model->getCommentCount(),
            'commentators' => $commentatorList,
            'participants' => $participantList,
            'links' => [
                'owner' => $author
            ]
        ];
    }
} 