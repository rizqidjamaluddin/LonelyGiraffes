<?php
use Giraffe\Buddies\BuddyModel;
use Giraffe\Buddies\Requests\BuddyRequestModel;
use Giraffe\Buddies\Requests\BuddyRequestTransformer;
use Giraffe\Common\Controller;
use Giraffe\Buddies\Requests\BuddyRequestService;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserTransformer;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BuddyRequestController extends Controller
{
    /**
     * @param \Giraffe\Buddies\Requests\BuddyRequestService $buddyRequestService
     */
    public function __construct(BuddyRequestService $buddyRequestService)
    {
        $this->buddyRequestService = $buddyRequestService;
        parent::__construct();
    }

    public function requestIndex($userHash)
    {
        if ($userFilter = Input::get('user')) {
            $models = $this->buddyRequestService->getBuddyRequestsBetweenUsers($userHash, $userFilter);
        } else {
            $models = $this->buddyRequestService->getBuddyRequests($userHash);
        }
        return $this->returnBuddyRequestModels($models);
    }

    public function outgoingIndex($userHash)
    {
        if ($userFilter = Input::get('user')) {
            $models = $this->buddyRequestService->getOutgoingBuddyRequestBetweenUsers($userHash, $userFilter);
        } else {
            $models = $this->buddyRequestService->getOutgoingBuddyRequests($userHash);
        }
        return $this->returnBuddyRequestModels($models);
    }

    public function create($userHash)
    {
        $buddyRequest = $this->buddyRequestService->createBuddyRequest($this->gatekeeper->me(), $userHash);
        return $this->returnBuddyRequestModel($buddyRequest);
    }

    public function accept($userHash, $requestHash)
    {
        $buddy = $this->buddyRequestService->acceptBuddyRequest($this->gatekeeper->me(), $requestHash);
        return ['message' => 'Request accepted'];
    }

    public function destroy($userHash, $targetHash)
    {
        $this->buddyRequestService->denyBuddyRequest($targetHash);
        return ['message' => 'Request denied'];
    }

    public function returnBuddyRequestModel(BuddyRequestModel $models)
    {
        return $this->withItem($models, new BuddyRequestTransformer(), 'buddy_requests');
    }

    public function returnBuddyRequestModels(\Illuminate\Support\Collection $models)
    {
        return $this->withCollection($models, new BuddyRequestTransformer(), 'buddy_requests');
    }

    /**
     * @param Collection $models
     *
     * @return \Illuminate\Http\Response
     */
    public function returnUserModels(Collection $models)
    {
        return $this->withCollection($models, new UserTransformer(), 'buddies');
    }
}