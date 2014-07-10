<?php

use Giraffe\Buddies\BuddyModel;
use Giraffe\BuddyRequests\BuddyRequestModel;
use Giraffe\BuddyRequests\BuddyRequestTransformer;
use Giraffe\Common\Controller;
use Giraffe\BuddyRequests\BuddyRequestService;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserTransformer;
use Illuminate\Database\Eloquent\Collection;

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

    public function index($userHash)
    {
        $models = $this->buddyRequestService->getBuddyRequests($userHash, Input::get('method'));
        return $this->returnBuddyRequestModels($models);
    }

    public function create($userHash)
    {
        $buddyRequest = $this->buddyRequestService->createBuddyRequest($userHash, Input::get("target"));
        return $this->returnBuddyRequestModel($buddyRequest);
    }

    public function accept($userHash, $targetHash)
    {
        $buddy = $this->buddyRequestService->acceptBuddyRequest($userHash, $targetHash);
        $users = $buddy->users()->get();;
        return $this->returnUserModels($users);
    }

    public function destroy($user_hash, $request)
    {

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
        return $this->withCollection($models, new UserTransformer(), 'users');
    }
}