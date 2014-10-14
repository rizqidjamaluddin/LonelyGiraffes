<?php  namespace Giraffe\Events; 
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class EventTransformer extends TransformerAbstract
{
    public function transform(EventModel $model)
    {
        $userTransformer = (new UserTransformer());
        $author = $userTransformer->transform($model->owner);

        $commentatorList = [];
        $commentators = $model->getCommentators();
        foreach ($commentators as $commentator) {
            $commentatorList[] = $userTransformer->transform($commentator);
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
            'links' => [
                'owner' => $author
            ]
        ];
    }
} 