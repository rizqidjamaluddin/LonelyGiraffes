<?php  namespace Giraffe\BuddyRequests;

use Eloquent;

class BuddyRequestModel extends Eloquent {
    protected $table = 'buddy_requests';
	protected $fillable = ['from_user_id', 'to_user_id', 'sent_time', 'seen_time'];

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