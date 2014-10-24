<?php  namespace Giraffe\Stickies; 
use Giraffe\Support\Transformer\Transformer;

class StickyTransformer extends Transformer
{
    public function getServedClass()
    {
        return StickyModel::class;
    }

    /**
     * @param StickyModel $model
     * @return array
     */
    public function transform($model)
    {

        return [
            'body' => $model->body,
            'html_body' => $model->html_body,
            'class' => $model->class,
            'timestamp' => (string) $model->created_at
        ];
    }

} 