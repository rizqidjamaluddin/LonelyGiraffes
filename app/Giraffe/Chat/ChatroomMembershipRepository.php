<?php  namespace Giraffe\Chat; 

use Giraffe\Common\EloquentRepository;

class ChatroomMembershipRepository extends EloquentRepository
{

    public function __construct(ChatroomMembershipModel $model)
    {
        parent::__construct($model);
    }

} 