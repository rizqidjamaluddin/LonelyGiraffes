<?php  namespace Giraffe\BuddyRequests;

use Eloquent;
use Giraffe\Common\HasEloquentHash;

class BuddyRequestModel extends Eloquent {
    use HasEloquentHash;

    protected $table = 'buddy_requests';
	protected $fillable = ['hash', 'from_user_id', 'to_user_id'];

    public function getResourceName()
    {
        return "buddy_request";
    }

    public function recipient()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'to_user_id');
    }

    public function sender()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'from_user_id');
    }
}