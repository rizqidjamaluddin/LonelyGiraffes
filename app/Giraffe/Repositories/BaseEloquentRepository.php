<?php  namespace Giraffe\Repositories; 

use anlutro\LaravelRepository\NotFoundException;
use Eloquent;
use Giraffe\Exceptions\DuplicateCreationException;
use Giraffe\Exceptions\InvalidCreationException;
use Giraffe\Exceptions\NotFoundModelException;
use Illuminate\Database\QueryException;
use stdClass;

/**
 * Class BaseEloquentRepository
 *
 * Basic repository with compatibility with eloquent-powered models.
 *
 * @package Giraffe\Repositories
 */
abstract class BaseEloquentRepository implements BaseRepository
{

    /**
     * @var Eloquent
     */
    protected $model;

    public function __construct(Eloquent $model)
    {
        $this->model = $model;
    }

     public function getById($id)
     {
         $model = $this->model->find($id);
         if (!$model) {
             throw new NotFoundModelException();
         }
         return $model;
     }

     public function getByHash($hash)
     {
         $model = $this->model->where('hash', '=', $hash)->first();
         if (!$model) {
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

     public function deleteById($id)
     {
         return $this->model->destroy($id);
     }

     public function deleteByHash($hash)
     {
         return $this->model->where('hash', '=', $hash)->delete();
     }

     public function deleteMany(array $ids)
     {
         return $this->model->destroy($ids);
     }
 }