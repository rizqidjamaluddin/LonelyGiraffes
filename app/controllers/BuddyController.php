<?php

use Giraffe\Common\Controller;
use Giraffe\Buddies\BuddyService;
use Giraffe\Users\UserTransformer;
use Illuminate\Support\Collection;

class BuddyController extends Controller
{
    protected $key = 'buddies';

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
        if ($userFilter = Input::get('user')) {
            $models = $this->buddyService->getUserIfBuddies($user_hash, $userFilter);
        } else {
            $models = $this->buddyService->getBuddies($user_hash);
        }
        return $this->returnUserModels($models);
    }

    public function destroy($user_hash, $target_hash)
    {
        $unbuddied = $this->buddyService->unbuddy($user_hash, $target_hash);
        return ['message' => "{$unbuddied->name} is no longer listed as a buddy"];
    }

    public function returnUserModels(Collection $models)
    {
        return $this->withCollection($models, new UserTransformer());
    }
}