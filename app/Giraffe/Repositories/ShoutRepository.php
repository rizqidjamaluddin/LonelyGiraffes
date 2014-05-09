<?php  namespace Giraffe\Repositories;

use Giraffe\Models\ShoutModel;

class ShoutRepository extends BaseEloquentRepository
{

    /**
     * @var \Giraffe\Models\ShoutModel
     */
    private $shoutModel;

    public function __construct(ShoutModel $shoutModel)
    {
        $this->shoutModel = $shoutModel;
    }

} 