<?php  namespace Giraffe\BuddyRequests;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class BuddyRequestTransformer extends TransformerAbstract
{

    public function transform($buddyRequestModel)
    {
        $userTransformer = new UserTransformer();

        $recipientUser = $userTransformer->transform($buddyRequestModel->recipient);
        $senderUser = $userTransformer->transform($buddyRequestModel->sender);

        return [
            'recipient' => $recipientUser,
            'sender' => $senderUser,
            'sent_time' => $buddyRequestModel->sent_time,
            'seen_time' => $buddyRequestModel->seen_time
        ];
    }
}