<?php  namespace Giraffe\Common;

use Eloquent;
use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Repository;
use Illuminate\Database\QueryException;
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
     * @var bool
     */
    protected $hasHash = false;

    public function __construct(Eloquent $model)
    {
        $this->model = $model;
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
         if (!$this->hasHash) {
             throw new NotFoundModelException();
         }

         if (!$model = $this->model->where('hash', '=', $hash)->first()) {
             throw new NotFoundModelException();
         }
         return $model;
     }

     public function create(array $attributes)
     {
         try {
             $model = $this->model->create($attributes);
         } catch (QueryException $e) {
             // error code for "duplicate in unique column"
             if ($e->errorInfo[0] == 23000) {
                throw new DuplicateCreationException;
             }
             throw new InvalidCreationException;
         }

         return $model;
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
         return true;
     }

     public function deleteByHash($hash)
     {
         if (!$this->model->where('hash', '=', $hash)->delete()) {
             throw new NotFoundModelException;
         };
         return true;
     }

     public function deleteMany(array $ids)
     {
         return $this->model->destroy($ids);
     }
 }
