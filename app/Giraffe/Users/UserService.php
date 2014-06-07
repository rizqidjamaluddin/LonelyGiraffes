<?php  namespace Giraffe\Users;
use Giraffe\Common\Service;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserRepository;
use Hash;
use Illuminate\Database\Eloquent\Model;
use stdClass;
use Str;

class UserService extends Service
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
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
    }

    /**
     * @param  array $data
     * @return UserModel
     */
    public function createUser($data) {
        $data = array_only($data, ['firstname', 'lastname', 'password', 'email', 'gender']);
        $this->creationValidator->validate($data);
        $data['password'] = Hash::make($data['password']);
        $data['hash'] = Str::random(30);
        $data['role'] = 'member';

        $user = $this->userRepository->create($data);
        $this->log->info($this, 'New user registered', $user);
        return $user;
    }


    public function changePassword($user_id, $new_password)
    {
        $user = $this->userRepository->get($user_id);
        $user->password = Hash::make($new_password);
        $user->save();
        return $user;
    }

    public function updateUser($user_id, $attributes)
    {
        // remove password from attributes
        unset($attributes['password']);
        return true;
    }

    public function deleteUser($id)
    {
        $user = $this->userRepository->get($id);
        $this->gatekeeper->mayI('delete', $user)->please();
        $user->delete();
        return $user;
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