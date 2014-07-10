<?php

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

    public function index($user_hash)
    {
        $models = $this->buddyRequestService->getBuddyRequests($user_hash, Input::get('method'));
        return $this->returnBuddyRequestModels($models);
    }

    public function create($user_hash)
    {
        $user = $this->buddyRequestService->createBuddyRequest($user_hash, Input::get("target"));
        return $this->returnBuddyRequestModel($user);
    }

    public function accept($user_hash, $request)
    {

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
}