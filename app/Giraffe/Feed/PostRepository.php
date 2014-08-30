<?php  namespace Giraffe\Feed;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\Internal\QueryFilter;
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

    public function getForUser($userId, QueryFilter $options)
    {
        $q = $this->model;
        $q = $this->appendOptions($q, $options);
        $q = $this->appendEagerLoads($q);
        $q = $q->where('user_id', $userId)
            ->orderBy('id', 'desc');
        return $q->get();
    }

    public function deleteForPostable(Postable $postable)
    {
        $this->model->where('postable_type', get_class($postable))->where('postable_id', $postable->getId())->delete();
    }

    /**
     * @param $options
     * @return Collection
     */
    public function getGlobal(QueryFilter $options)
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
    protected function appendOptions($query, QueryFilter $options)
    {
        $query = $query->take($options->get('take'));
        if ($after = $options->get('after')) {
            $query = $query->where('id', '>', $after->id);
        }
        if ($before = $options->get('before')) {
            $query = $query->where('id', '<', $before->id);
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