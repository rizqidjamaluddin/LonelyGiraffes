<?php  namespace Giraffe\Buddies\Events;

use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Common\Event;

class BuddyRequestSentEvent extends Event
{

    /**
     * @var BuddyRequestModel
     */
    private $buddyRequestModel;

    public function __construct(BuddyRequestModel $buddyRequestModel)
    {
        $this->buddyRequestModel = $buddyRequestModel;
    }

    public function getRecipient()
    {
        return $this->buddyRequestModel->recipient();
    }
} 