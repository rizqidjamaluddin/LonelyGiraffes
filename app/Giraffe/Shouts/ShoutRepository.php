<?php  namespace Giraffe\Shouts;

use Giraffe\Common\EloquentRepository;
use Giraffe\Shouts\ShoutModel;

class ShoutRepository extends EloquentRepository
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