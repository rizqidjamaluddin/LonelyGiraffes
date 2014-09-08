<?php  namespace Giraffe\Notifications\SystemNotification;

use Giraffe\Notifications\SystemNotification\SystemNotificationModel;
use League\Fractal\TransformerAbstract;
use stdClass;

class SystemNotificationTransformer extends TransformerAbstract
{
    public function transform(SystemNotificationModel $model)
    {
        return [
            'title' => $model->title,
            'message' => $model->message
        ];
    }
} 