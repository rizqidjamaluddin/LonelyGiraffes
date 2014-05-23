<?php  namespace Giraffe\Shouts;

use Giraffe\Common\BaseEloquentRepository;
use Giraffe\Shouts\ShoutModel;

class ShoutRepository extends BaseEloquentRepository
{

    /**
     * @var \Giraffe\Shouts\ShoutModel
     */
    private $shoutModel;

    public function __construct(ShoutModel $shoutModel)
    {
        $this->shoutModel = $shoutModel;
    }

} 