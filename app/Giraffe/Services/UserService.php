<?php  namespace Giraffe\Services; 
use Giraffe\Models\UserModel;
use Giraffe\Repositories\UserRepository;
use stdClass;

class UserService
{

    /**
     * @var \Giraffe\Repositories\UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param int    $user
     * @param string $email
     *
     * @return bool
     */
    public function deactivateUser($user, $email)
    {
        return (bool) $this->userRepository->deleteByIdWithEmailConfirmation($user, $email);
    }

    /**
     * @param int    $user 
     * @return bool
     */
    public function reactivateUser($user) 
    {
        return (bool) $this->userRepository->reactivateById($user);
    }

    /**
     * @param  int  $user
     * @return bool
     */
    public function getUserNicknameSetting($user)
    {
        return (bool) $this->userRepository->getByIdWithSettings($user)->settings->use_nickname;
    }

    /**
     * @param int  $user
     * @param bool $useNickname
     *
     * @return bool
     */
    public function setUserNicknameSetting($user, $useNickname)
    {
        return (bool) $this->userRepository->setUserNicknameSettingById($user, $useNickname);
    }

} 