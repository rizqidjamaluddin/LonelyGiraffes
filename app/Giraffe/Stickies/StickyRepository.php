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
        return $this->model->remember(100)->cacheTags('lg-sticky')->get();
    }

    public function cleanOld()
    {
        $this->getCache()->tags(['lg-sticky'])->flush();
        $this->model->truncate();
    }
} 