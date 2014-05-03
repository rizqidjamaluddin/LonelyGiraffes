<?php  namespace Giraffe\Repositories; 

use anlutro\LaravelRepository\NotFoundException;
use Eloquent;
use Giraffe\Exceptions\NotFoundModelException;
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
         return $this->model->create($attributes);
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