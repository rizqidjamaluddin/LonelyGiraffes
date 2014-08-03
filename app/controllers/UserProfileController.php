<?php
use Giraffe\Users\ProfileService;
use Giraffe\Users\ProfileTransformer;

class UserProfileController extends \Giraffe\Common\Controller
{

    /**
     * @var ProfileService
     */
    private $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show($user)
    {
        $profile = $this->profileService->getUserProfile($user);
        return $this->withItem($profile, new ProfileTransformer(), 'profiles');
    }

    public function update($user)
    {
        $profile = $this->profileService->updateUserProfile($user, Input::all());
        return $this->withItem($profile, new ProfileTransformer(), 'profiles');
    }

} 