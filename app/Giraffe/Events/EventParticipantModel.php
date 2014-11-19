<?php  namespace Giraffe\Events; 

use Giraffe\Support\Transformer\Transformable;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Database\Eloquent\Model;

class EventParticipantModel extends Model implements Transformable
{
    protected $table = 'event_participants';
    protected $fillable = ['user_id', 'event_id'];

    public static function join(EventModel $event, UserModel $user)
    {
        $instance = new self;
        $instance->user_id = $user->id;
        $instance->event_id = $event->id;
        return $instance;
    }

    public function getUser()
    {
        /** @var UserRepository $userRepository */
        $userRepository = \App::make(UserRepository::class);
        return $userRepository->getById($this->user_id);
    }

} 