<?php  namespace Giraffe\Passwords; 
use Carbon\Carbon;
use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserModel;

class ResetTokenRepository extends EloquentRepository
{
    public function __construct(ResetTokenModel $tokenModel)
    {
        parent::__construct($tokenModel);
    }

    public function countIssuedInLastMinuteFor(UserModel $user)
    {
        return $this->model->where('user_id', $user->id)->where('created_at', '>', Carbon::now()->subMinute())->count();
    }

    public function countIssuedFor(UserModel $user)
    {
        return $this->model->where('user_id', $user->id)->count();
    }

    public function purgeExpired()
    {
        return $this->model->where('expires_at', '<', Carbon::now())->delete();
    }

    public function getByToken($token)
    {
        $token = $this->model->where('token', $token)->first();
        if (!$token) {
            throw new NotFoundModelException;
        }
        return $token;
    }
} 