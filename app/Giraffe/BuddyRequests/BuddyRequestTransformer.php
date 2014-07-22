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
            'hash' => $buddyRequestModel->hash,
            'recipient' => $recipientUser,
            'sender' => $senderUser,
            'sent_timestamp' => (string) $buddyRequestModel->created_at
        ];
    }
}