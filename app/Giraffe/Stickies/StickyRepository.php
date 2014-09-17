<?php  namespace Giraffe\Stickies; 
use Giraffe\Common\EloquentRepository;

class StickyRepository extends EloquentRepository
{
    public function __construct(StickyModel $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return $this->model->all();
    }

    public function cleanOld()
    {
        $this->model->truncate();
    }
} 