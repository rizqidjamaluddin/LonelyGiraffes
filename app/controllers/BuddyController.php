<?php

use Giraffe\Common\Controller;
use Giraffe\Buddies\BuddyService;
use Giraffe\Users\UserTransformer;
use Illuminate\Database\Eloquent\Collection;

class BuddyController extends Controller
{
    /**
     * @param Giraffe\Buddies\BuddyService $buddyService
     */
    public function __construct(BuddyService $buddyService)
    {
        $this->buddyService = $buddyService;
        parent::__construct();
    }

    public function index($user_hash)
    {
        $models = $this->buddyService->getBuddies($user_hash);
        return $this->returnUserModels($models);
    }

    public function destroy($user_hash)
    {
        if(!Input::get('target'))
            throw new BadRequestHttpException();
        $this->buddyService->unbuddy($user_hash, Input::get('target'));
        return [];
    }

    public function returnUserModels(Collection $models)
    {
        return $this->withCollection($models, new UserTransformer(), 'users');
    }
}