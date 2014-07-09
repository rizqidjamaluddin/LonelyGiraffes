<?php  namespace Giraffe\BuddyRequests;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;

class BuddyRequestRepository extends EloquentRepository
{

    public function __construct(BuddyRequestModel $buddyRequestModel)
    {
        parent::__construct($buddyRequestModel);
    }
} 