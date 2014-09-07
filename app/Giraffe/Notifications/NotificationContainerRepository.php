<?php  namespace Giraffe\Notifications; 

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\Internal\QueryFilter;
use Illuminate\Support\Collection;

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
    public function getForUser($userId, QueryFilter $filter)
    {
        $q = $this->model;
        $q = $this->appendLimitingOptions($q, $filter);

        return $q->where('user_id', $userId)->get();
    }

    protected function appendLimitingOptions($q, QueryFilter $filter)
    {

        if ($only = $filter->get('only')) {
            $onlyFilter = new Collection(explode(',', $only));
            $q = $q->whereIn('notification_type', $onlyFilter->toArray());
        }

        if ($except = $filter->get('except')) {
            $exceptFilter = new Collection(explode(',', $except));
            $q = $q->whereNotIn('notification_type', $exceptFilter->toArray());
        }

        return $q;
    }

} 