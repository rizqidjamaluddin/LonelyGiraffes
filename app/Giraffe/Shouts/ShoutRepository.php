<?php  namespace Giraffe\Shouts;

use Giraffe\Common\EloquentRepository;
use Giraffe\Shouts\ShoutModel;

class ShoutRepository extends EloquentRepository
{

    public function __construct(ShoutModel $shoutModel)
    {
        parent::__construct($shoutModel);
    }

} 