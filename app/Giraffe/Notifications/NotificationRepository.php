<?php  namespace Giraffe\Notifications; 

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\Internal\QueryFilter;
use Illuminate\Support\Collection;

class NotificationRepository extends EloquentRepository
{
    public function __construct(NotificationModel $containerModel)
    {
        parent::__construct($containerModel);
    }

    /**
     * @param $userId
     *
     * @return NotificationModel[]
     */
    public function getForUser($userId, QueryFilter $filter)
    {
        $q = $this->model;
        $q = $this->appendLimitingOptions($q, $filter);

        return $q->where('user_id', $userId)->orderBy('id', 'desc')->get();
    }


    public function getUnreadForUser($userId, QueryFilter $filter)
    {
        $q = $this->model;
        $q = $this->appendLimitingOptions($q, $filter);

        return $q->where('user_id', $userId)->where('read', 0)->orderBy('id', 'desc')->get();
    }

    protected function appendLimitingOptions($q, QueryFilter $filter)
    {

        $q = $q->take($filter->get('take'));
        if ($after = $filter->get('after')) {
            $q = $q->where('id', '>', $after->id);
        }
        if ($before = $filter->get('before')) {
            $q = $q->where('id', '<', $before->id);
        }

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

    public function countUnreadForUser($id)
    {
        return $this->model->where('user_id', $id)->where('read', 0)->count();
    }

} 