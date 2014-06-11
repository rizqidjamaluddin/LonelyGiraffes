<?php  namespace Giraffe\Notifications; 

use Giraffe\Common\EloquentRepository;

class NotificationContainerRepository extends EloquentRepository
{
    public function __construct(NotificationContainerModel $containerModel)
    {
        parent::__construct($containerModel);
    }
} 