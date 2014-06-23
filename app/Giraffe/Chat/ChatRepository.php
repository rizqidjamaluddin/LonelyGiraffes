<?php

use Giraffe\Common\EloquentRepository;
use Giraffe\Chat\ConversationModel;


class ChatRepository extends EloquentRepository
{
	public function __construct(ConversationModel $conversationModel)
    {
        parent::__construct($conversationModel);
    }
}