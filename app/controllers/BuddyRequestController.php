<?php

use Giraffe\Common\Controller;
use Giraffe\BuddyRequests\BuddyRequestService;

class BuddyRequestController extends Controller
{
    /**
     * @param Giraffe\BuddyRequests\BuddyRequestService $buddyRequestService
     */
    public function __construct(BuddyRequestService $buddyRequestService)
    {
        $this->$buddyRequestService = $buddyRequestService;
        parent::__construct();
    }

    public function index($user_hash)
    {
        $models = $this->buddyRequestService->getBuddyRequests($user_hash);
        return $this->returnUserModels($models);
    }

    public function create($user_hash)
    {
        return $this->buddyRequestService->createBuddyRequest($user_hash, Input::get("target"));
    }

    public function accept($user_hash, $request)
    {

    }

    public function destroy($user_hash, $request)
    {

    }
}