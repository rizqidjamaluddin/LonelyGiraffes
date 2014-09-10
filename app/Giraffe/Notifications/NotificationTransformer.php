<?php  namespace Giraffe\Notifications; 

use Dingo\Api\Transformer\TransformableInterface;
use Giraffe\Common\Transformable;
use League\Fractal\TransformerAbstract;
use stdClass;

class NotificationTransformer extends TransformerAbstract
{
    public function transform(NotificationModel $model)
    {

        $body = $model->notifiable();

        if ($body instanceof Transformable) {
            $transformer = $body->getTransformer();
            $body = $transformer->transform($body);
        }

        return [
            'hash' => $model->hash,
         'type' => $model->notifiable()->getType(),
         'timestamp' => (string) $model->created_at,
         'attached' => $body,
        ];
    }

} 