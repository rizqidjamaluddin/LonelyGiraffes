<?php  namespace Giraffe\Common;

use App;
use Eloquent;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Repository;
use Giraffe\Logging\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Class EloquentRepository
 *
 * Basic repository with compatibility with eloquent-powered models.
 *
 * @package Giraffe\Repositories
 */
abstract class EloquentRepository implements Repository
{

    /**
     * @var Eloquent
     */
    protected $model;

    /**
     * @var Log
     */
    protected $log;

    public function __construct(Eloquent $model)
    {
        $this->model = $model;
        $this->log = App::make('Giraffe\Logging\Log');
    }

    /**
     * @param string|int|Eloquent $idOrHash
     *
     * @return mixed
     */
    public function get($idOrHash)
    {

        if ($idOrHash instanceof Eloquent) {
            return $idOrHash;
        }

        // is_numeric and other detectors may return false positives with hashes.
        // instead, attempt to load by ID, and if that fails, load by hash.
        try {
            $model = $this->getById($idOrHash);
            return $model;
        } catch (NotFoundModelException $e) {
            return $this->getByHash($idOrHash);
        }
    }

    public function getById($id)
    {
        if (!$model = $this->model->find($id)) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    public function getByHash($hash)
    {
        if ($hash instanceof Eloquent) {
            return $hash;
        }

        if (!$this->model->hasHash) {
            throw new NotFoundModelException();
        }

        if (!$model = $this->model->where('hash', '=', $hash)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|array $collection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMany($collection)
    {
        if (count($collection) == 0) return new Collection;
        return $this->model->whereIn('id', $collection)->get();
    }

    public function create(array $attributes)
    {
        try {
            $model = $this->model->create($attributes);
        } catch (QueryException $e) {
            $this->log->info(__CLASS__, "Invalid Creation", $e->getMessage());
            // error code for "duplicate in unique column"
            if ($e->errorInfo[0] == 23000) {
                throw new DuplicateCreationException;
            }
            throw new InvalidCreationException;
        }

        return $model;
    }

    public function update($identifier, Array $attributes)
    {
        $model = $this->get($identifier);
        foreach ($attributes as $property => $attribute) {
            $model->$property = $attribute;
        }
        try {
            $model->save();
        } catch (QueryException $e) {
            $this->log->info(__CLASS__, "Invalid Update", $e->getMessage());
            if ($e->errorInfo[0] == 23000) {
                throw new DuplicateUpdateException;
            }
            throw new InvalidUpdateException;
        }
        return $model;
    }

    public function save(Model $model)
    {
        $model->save();
        return true;
    }

    public function delete($identifier)
    {

        if (is_array($identifier)) {
            foreach ($identifier as $subIdentifier) {
                $this->delete($subIdentifier);
            }
        }

        if ($identifier instanceof Eloquent) {
            return $identifier->delete();
        }

        try {
            $delete = $this->deleteById($identifier);
            return $delete;
        } catch (NotFoundModelException $e) {
            return $this->deleteByHash($identifier);
        }
    }

    public function deleteById($id)
    {
        if (!$this->model->destroy($id)) {
            throw new NotFoundModelException;
        };
        return $this->model->find($id);
    }

    public function deleteByHash($hash)
    {
        if (!$this->model->where('hash', '=', $hash)->delete()) {
            throw new NotFoundModelException;
        };
        return $this->model->find($hash);
    }

    public function deleteMany(array $ids)
    {
        return $this->model->destroy($ids);
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        $cache = $this->model->getConnection()->getCacheManager()->driver();
        return $cache;
    }
}
