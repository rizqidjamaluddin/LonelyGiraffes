<?php  namespace Giraffe\Passwords; 
use Giraffe\Common\EloquentRepository;

class ResetTokenRepository extends EloquentRepository
{
    public function __construct(ResetTokenModel $tokenModel)
    {
        parent::__construct($tokenModel);
    }
} 