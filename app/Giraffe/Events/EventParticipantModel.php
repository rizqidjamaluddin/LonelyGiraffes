<?php  namespace Giraffe\Events; 

use Illuminate\Database\Eloquent\Model;

class EventParticipantModel extends Model
{
    protected $table = 'event_participants';
    protected $fillable = ['user_id', 'event_id'];
} 