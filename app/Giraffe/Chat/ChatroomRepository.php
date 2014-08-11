<?php namespace Giraffe\Chat;

use Giraffe\Chat\ChatroomModel;
use Giraffe\Common\EloquentRepository;
use Giraffe\Chat\ConversationModel;
use Giraffe\Users\UserModel;


class ChatroomRepository extends EloquentRepository
{
	public function __construct(ChatroomModel $conversationModel)
    {
        parent::__construct($conversationModel);
    }

    public function findForMembership(ChatroomMembershipModel $membershipModel)
    {
        return $this->model->where('id', $membershipModel->conversation_id)->first();
    }
}