<?php  namespace Giraffe\Events; 
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class EventTransformer extends TransformerAbstract
{
    public function transform(EventModel $model)
    {
        $author = (new UserTransformer())->transform($model->owner);

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
            'links' => [
                'owner' => $author
            ]
        ];
    }
} 