<?php  namespace Giraffe\Stickies; 

use Giraffe\Common\Service;

class StickyService extends Service
{

    /**
     * @var StickyRepository
     */
    private $repository;

    public function __construct(StickyRepository $repository)
    {
        $this->repository = $repository;
        parent::__construct();
    }

    public function getStickies()
    {
        return $this->repository->all();
    }

    public function post($body)
    {
        $sticky = StickyModel::post($body);

        // delete old sticky first, if applicable
        $this->clear();

        $this->repository->save($sticky);
        return $sticky;
    }

    public function clear()
    {
        $this->repository->cleanOld();
    }
}