<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Users\UserRepository;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ChatMessageModel extends Eloquent {
    use HasEloquentHash, SoftDeletingTrait;

    protected $table = 'chat_messages';
	protected $fillable = ['user_id', 'body', 'html_body', 'chatroom_id', 'hash'];

    public function author()
    {
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);

        return $userRepository->get($this->user_id);
    }
}