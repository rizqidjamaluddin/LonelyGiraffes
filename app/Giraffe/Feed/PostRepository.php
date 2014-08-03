<?php  namespace Giraffe\Feed;

use Giraffe\Common\EloquentRepository;
use Giraffe\Feed\Postable;
use Giraffe\Feed\PostModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class PostRepository extends EloquentRepository
{

    public function __construct(PostModel $postModel)
    {
        parent::__construct($postModel);
    }

    public function getByHashWithPostable($hash)
    {
        return $this->model->with('postable', 'postable.author')->where('hash', $hash)->first();
    }

    public function getForUser($userId, $options)
    {
        $q = $this->model;
        $q = $this->appendOptions($q, $options);
        $q = $this->appendEagerLoads($q);
        $q = $q->where('user_id', $userId)
            ->orderBy('id', 'desc');
        return $q->get();
    }

    /**
     * @param $options
     * @return Collection
     */
    public function getGlobal($options = [])
    {
        $q = $this->model;
        $q = $this->appendOptions($q, $options);
        $q = $this->appendEagerLoads($q);
        return $q->orderBy('id', 'desc')->get();
    }

    /**
     * @param Builder $query
     * @param         $options
     * @return \Illuminate\Database\Query\Builder
     */
    protected function appendOptions($query, $options)
    {
        $take = array_get($options, 'take') ?: 10;
        $query = $query->take($take);
        if ($after = array_get($options, 'after')) {
            $query = $query->where('id', '>', $after);
        }
        if ($before = array_get($options, 'before')) {
            $query = $query->where('id', '<', $before);
        }
        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    protected function appendEagerLoads($query)
    {
        return $query->with('postable');
    }

} 