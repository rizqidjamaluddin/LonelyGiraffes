<?php  namespace Giraffe\Notifications; 

use Dingo\Api\Transformer\TransformableInterface;
use League\Fractal\TransformerAbstract;
use stdClass;

class NotificationTransformer extends TransformerAbstract
{
    public function transform(NotificationModel $model)
    {

        $body = $model->notification;

        if ($body instanceof TransformableInterface) {
            $transformer = $body->getTransformer();
            $body = $transformer->transform($body);
        }

        return [
         'type' => $model->notification->getType(),
         'timestamp' => (string) $model->created_at,
         'body' => $body,
        ];
    }

} 