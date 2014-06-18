<?php  namespace Giraffe\Notifications; 

use Giraffe\Common\EloquentRepository;

class NotificationContainerRepository extends EloquentRepository
{
    public function __construct(NotificationContainerModel $containerModel)
    {
        parent::__construct($containerModel);
    }

    /**
     * @param $userId
     *
     * @return NotificationContainerModel[]
     */
    public function getForUser($userId)
    {
        return $this->model->with('notification')->where('user_id', $userId)->get();
    }

} 