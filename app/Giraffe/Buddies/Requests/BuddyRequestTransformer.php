<?php  namespace Giraffe\Buddies\Requests;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class BuddyRequestTransformer extends TransformerAbstract
{

    public function transform(BuddyRequestModel $buddyRequestModel)
    {
        $userTransformer = new UserTransformer();

        $recipientUser = $userTransformer->transform($buddyRequestModel->recipient());
        $senderUser = $userTransformer->transform($buddyRequestModel->sender());

        $links = [];
        $links['accept_url'] = url($buddyRequestModel->recipient()->hash . '/buddy-requests/' . $buddyRequestModel->hash . '/accept');
        $links['accept_method'] = 'POST';
        $links['deny_url'] = url($buddyRequestModel->recipient()->hash . '/buddy-requests/' . $buddyRequestModel->hash);
        $links['deny_method'] = 'DELETE';

        return [
            'hash' => $buddyRequestModel->hash,
            'recipient' => $recipientUser,
            'sender' => $senderUser,
            'sent_timestamp' => (string) $buddyRequestModel->created_at,
            'links' => $links,
        ];
    }
}