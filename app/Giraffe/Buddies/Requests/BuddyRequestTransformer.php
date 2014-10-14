<?php  namespace Giraffe\Buddies\Requests;

use Giraffe\Common\Value\ApiAction;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

class BuddyRequestTransformer extends Transformer
{

    /**
     * @param BuddyRequestModel $buddyRequestModel
     * @return array
     */
    public function transform($buddyRequestModel)
    {
        $userTransformer = new UserTransformer();

        $recipientUser = $userTransformer->transform($buddyRequestModel->recipient());
        $senderUser = $userTransformer->transform($buddyRequestModel->sender());

        $links = $this->generateActions($buddyRequestModel);
        array_walk($links, function(ApiAction $a) {
               return $a->toArray();
            });

        return [
            'hash'           => $buddyRequestModel->hash,
            'recipient'      => $recipientUser,
            'sender'         => $senderUser,
            'sent_timestamp' => (string) $buddyRequestModel->created_at,
            'links'          => $links,
        ];
    }

    /**
     * @param BuddyRequestModel $buddyRequestModel
     * @return array
     */
    public function generateActions(BuddyRequestModel $buddyRequestModel)
    {
        $links = [];
        $links['accept'] = new ApiAction(
            '/api/users/' . $buddyRequestModel->recipient()->hash . '/buddy-requests/' . $buddyRequestModel->hash . '/accept',
            'POST', 'Accept'
        );
        $links['deny'] = new ApiAction(
            '/api/users/' . $buddyRequestModel->recipient()->hash . '/buddy-requests/' . $buddyRequestModel->hash, 'DELETE', 'Deny'
        );

        return $links;
    }
}