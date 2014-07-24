<?php

use Giraffe\Buddies\BuddyModel;
use Giraffe\BuddyRequests\BuddyRequestModel;
use Giraffe\BuddyRequests\BuddyRequestTransformer;
use Giraffe\Common\Controller;
use Giraffe\BuddyRequests\BuddyRequestService;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserTransformer;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BuddyRequestController extends Controller
{
    /**
     * @param Giraffe\BuddyRequests\BuddyRequestService $buddyRequestService
     */
    public function __construct(BuddyRequestService $buddyRequestService)
    {
        $this->buddyRequestService = $buddyRequestService;
        parent::__construct();
    }

    public function requestIndex($userHash)
    {
        $models = $this->buddyRequestService->getBuddyRequests($userHash);
        return $this->returnBuddyRequestModels($models);
    }

    public function outgoingIndex($userHash)
    {
        $models = $this->buddyRequestService->getOutgoingBuddyRequests($userHash);
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

    public function returnBuddyRequestModels(Collection $models)
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