<?php  namespace Giraffe\Notifications;

use Dingo\Api\Transformer\TransformableInterface;
use Giraffe\Support\Transformer\Transformable;
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
        $actionAttachments = $this->processAttachments($model);

        return [
            'hash'      => $model->hash,
            'type'      => $model->notifiable()->getType(),
            'read'      => $model->read,
            'timestamp' => (string)$model->created_at,
            'links'     => $linkAttachments,
            'actions'   => $actionAttachments,
            'body'      => $model->notifiable()->getMessage(),
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

    /**
     * @param NotificationModel $model
     * @return array
     */
    protected function processAttachments(NotificationModel $model)
    {
        $actionAttachments = [];
        $actions = $model->notifiable()->getActions();
        foreach ($actions as $name => $action) {
            $actionAttachments[$name] = $action->toArray();
        }
        return $actionAttachments;
    }

} 