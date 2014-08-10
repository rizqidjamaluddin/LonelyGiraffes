<?php namespace Giraffe\Chat;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Illuminate\Support\Collection;

class ChatroomModel extends Eloquent
{
    use HasEloquentHash, SoftDeletingTrait;

    protected $table = 'chatrooms';
    protected $fillable = ['name', 'hash'];

    public function memberships()
    {
        return $this->hasMany('\Giraffe\Chat\ChatroomMembershipModel', 'conversation_id');
    }

    /**
     * @return Collection;
     */
    public function participants()
    {
        return $this->memberships()->with('user')->get();
    }
}