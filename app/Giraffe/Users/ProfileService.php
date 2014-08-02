<?php  namespace Giraffe\Users;

use Giraffe\Common\Service;
use Giraffe\Parser\Parser;

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
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        UserRepository $userRepository,
        UserProfileRepository $profileRepository,
        UserProfileValidator $profileValidator,
        Parser $parser
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileValidator = $profileValidator;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
        parent::__construct();
    }

    public function getUserProfile($user)
    {
        $user = $this->userRepository->getByHash($user);
        $profile = $this->profileRepository->getForUserId($user->id);
        // we'll give a blank model back if the user has no profile settings
        if (!$profile) {
            $profile = UserProfileModel::createForUser($user);
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
            $profile = UserProfileModel::createForUser($user);
        }

        // gatekeeper check
        $this->gatekeeper->mayI('update', $profile)->please();

        if (array_key_exists('bio', $attributes)) {
            $profile->bio = $attributes['bio'];
            $profile->html_bio = $this->parser->parseLinks($attributes['bio']);
        }

        $this->profileRepository->save($profile);

        return $this->getUserProfile($user);
    }
} 