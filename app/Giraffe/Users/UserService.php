<?php  namespace Giraffe\Users;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class UserService
{

    /**
     * @var \Giraffe\Users\UserRepository
     */
    private $userRepository;
    /**
     * @var UserCreationValidator
     */
    private $creationValidator;

    public function __construct(UserRepository $userRepository, UserCreationValidator $creationValidator)
    {
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
    }

    /**
     * @param  array $info
     * @return Model|static
     */
    public function createUser($info) {
        $info['public_id'] = \Str::random(30);
        $this->creationValidator->validate($info);
        return $this->userRepository->create($info);
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function getUser($id) {
        return $this->userRepository->get($id);
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