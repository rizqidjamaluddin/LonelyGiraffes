<?php  namespace Giraffe\BuddyRequests;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;

class BuddyRequestRepository extends EloquentRepository
{

    public function __construct(BuddyModel $buddyModel)
    {
        parent::__construct($buddyModel);
    }

} 