<?php  namespace Giraffe\BuddyRequests;

use Eloquent;

class BuddyRequestModel extends Eloquent {
    protected $table = 'buddy_requests';
	protected $fillable = ['from_user_id', 'to_user_id', 'sent_time', 'seen_time'];

    public function getResourceName()
    {
        return "buddy_request";
    }
}