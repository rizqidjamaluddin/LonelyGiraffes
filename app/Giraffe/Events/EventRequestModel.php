<?php namespace Giraffe\Events;

use Eloquent;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Support\Transformer\Transformable;

class EventRequestModel extends Eloquent implements Transformable{
    use HasEloquentHash;

    protected $table = 'event_requests';
	protected $fillable = ['event_id', 'user_id', 'invitee_id'];

    public function event()
    {
        return $this->belongsTo('Giraffe\Events\EventModel');
    }

}