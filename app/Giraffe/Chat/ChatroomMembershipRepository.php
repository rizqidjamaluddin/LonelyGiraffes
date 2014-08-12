<?php  namespace Giraffe\Chat; 

use Giraffe\Common\EloquentRepository;
use Giraffe\Users\UserModel;

class ChatroomMembershipRepository extends EloquentRepository
{

    public function __construct(ChatroomMembershipModel $model)
    {
        parent::__construct($model);
    }

    public function findForUser(UserModel $userModel)
    {
        return $this->model->where('user_id', $userModel->id)->get();
    }

    public function findForUserInRoom($userModel, $chatroom)
    {
        return $this->model->where('user_id', $userModel->id)->where('conversation_id', $chatroom->id)
            ->first();
    }

} 