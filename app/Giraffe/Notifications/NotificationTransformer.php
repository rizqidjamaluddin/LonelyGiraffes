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

        $linkAttachments = $this->processLinks($model);

        return [
            'hash'      => $model->hash,
            'type'      => $model->notifiable()->getType(),
            'timestamp' => (string) $model->created_at,
            'links'  => $linkAttachments,
            'body'  => $model->notifiable()->getMessage(),
        ];
    }

    /**
     * @param NotificationModel $model
     * @return array
     */
    protected function processLinks(NotificationModel $model)
    {
        $links = $model->notifiable()->getLinks();
        $linkAttachments = [];
        foreach ($links as $link => $entity) {
            if ($entity instanceof Transformable) {
                $linkAttachments[$link] = $entity->getTransformer()->transform($entity);
            } else {
                $linkAttachments[$link] = $entity;
            }
        }
        return $linkAttachments;
    }

} 