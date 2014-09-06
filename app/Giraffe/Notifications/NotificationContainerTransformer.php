<?php  namespace Giraffe\Notifications; 

use Dingo\Api\Transformer\TransformableInterface;
use League\Fractal\TransformerAbstract;
use stdClass;

class NotificationContainerTransformer extends TransformerAbstract
{
    public function transform(NotificationContainerModel $model)
    {

        $body = $model->notification;

        if ($body instanceof TransformableInterface) {
            $transformer = $body->getTransformer();
            $body = $transformer->transform($body);
        }

        return [
         'type' => $model->notification->getClass(),
         'timestamp' => (string) $model->created_at,
         'body' => $body,
        ];
    }

} 