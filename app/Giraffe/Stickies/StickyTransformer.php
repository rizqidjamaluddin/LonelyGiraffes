<?php  namespace Giraffe\Stickies; 
use League\Fractal\TransformerAbstract;

class StickyTransformer extends TransformerAbstract
{

    public function transform(StickyModel $model)
    {

        return [
            'body' => $model->body,
            'html_body' => $model->html_body,
            'timestamp' => (string) $model->created_at
        ];
    }

} 