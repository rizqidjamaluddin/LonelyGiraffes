<?php namespace Giraffe\Chat;

use Giraffe\Chat\ChatroomModel;
use Giraffe\Common\EloquentRepository;
use Giraffe\Chat\ConversationModel;


class ChatroomRepository extends EloquentRepository
{
	public function __construct(ChatroomModel $conversationModel)
    {
        parent::__construct($conversationModel);
    }
}