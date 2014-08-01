<?php  namespace Giraffe\Users;

use Giraffe\Common\Service;

class ProfileService extends Service
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var UserProfileRepository
     */
    private $profileRepository;
    /**
     * @var UserProfileValidator
     */
    private $profileValidator;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository,
        UserProfileRepository $profileRepository,
        UserProfileValidator $profileValidator
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileValidator = $profileValidator;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    public function getUserProfile($user)
    {
        $user = $this->userRepository->getByHash($user);
        $profile = $this->profileRepository->getForUserId($user->id);
        // we'll give a blank model back if the user has no profile settings
        if (!$profile) {
            $profile = new UserProfileModel();
        }
        return $profile;
    }

    public function updateUserProfile($user, $attributes)
    {
        $user = $this->userRepository->getByHash($user);
        $attributes = array_only($attributes, ['bio']);

        $this->profileValidator->validate($attributes);

        $profile = $this->profileRepository->getForUserId($user->id);
        if (!$profile) {
            $profile = new UserProfileModel();
            $profile->user_id = $user->id;
        }

        // gatekeeper check
        $this->gatekeeper->mayI('update', $profile)->please();

        $profile->bio = $attributes['bio'];
        $profile->html_bio = $attributes['bio'];
        $this->profileRepository->save($profile);

        return $this->getUserProfile($user);
    }
} 