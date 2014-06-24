<?php  namespace Giraffe\Shouts;

use Giraffe\Common\EloquentRepository;
use Giraffe\Shouts\ShoutModel;

class ShoutRepository extends EloquentRepository
{

    public function __construct(ShoutModel $shoutModel)
    {
        parent::__construct($shoutModel);
    }

    public function getAllShoutsForUser($userId)
    {
    	return $this->model->where('user_id', '=', $userId)->get();
    }

} 