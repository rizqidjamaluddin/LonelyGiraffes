<?php  namespace Giraffe\Chat; 
use Giraffe\Common\EloquentRepository;

class ChatMessageRepository extends EloquentRepository
{
    public function __construct(ChatMessageModel $chatMessageModel)
    {
        parent::__construct($chatMessageModel);
    }
} 