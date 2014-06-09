<?php  namespace Giraffe\Users;

use Giraffe\Authorization\GatekeeperException;
use Giraffe\Common\InvalidUpdateException;
use Giraffe\Common\Service;
use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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
    /**
     * @var UserUpdateValidator
     */
    private $updateValidator;

    public function __construct(
        UserRepository $userRepository,
        UserCreationValidator $creationValidator,
        UserUpdateValidator $updateValidator
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
        $this->updateValidator = $updateValidator;
    }

    /**
     * @param  array $data
     *
     * @return UserModel
     */
    public function createUser($data)
    {
        $data = array_only($data, ['firstname', 'lastname', 'password', 'email', 'gender']);
        $this->creationValidator->validate($data);

        $data['password'] = Hash::make($data['password']);
        $data['hash'] = Str::random(32);
        $data['role'] = 'member';

        $user = $this->userRepository->create($data);
        $this->log->info($this, 'New user registered', $user->toArray());
        return $user;
    }

    /**
     * @param  int   $user_id
     * @param  array $attributes
     *
     * @return UserModel|null $userModel
     */
    public function updateUser($user_id, $attributes)
    {

        $acceptableAttributes = ['firstname', 'lastname', 'email', 'gender', 'password'];
        $attributes = array_only($attributes, $acceptableAttributes);

        $user = $this->userRepository->get($user_id);
        $this->gatekeeper->mayI('update', $user)->please();

        $this->updateValidator->validate($attributes);

        if (array_key_exists('password', $attributes)) {
            $user->password = Hash::make($attributes['password']);
        }

        $this->userRepository->update($user, $attributes);
        return $user;
    }

    /**
     * @param  int $id
     *
     * @throws \Exception
     * @return \Giraffe\Users\UserModel|null $userModel
     */
    public function deleteUser($id)
    {
        $user = $this->userRepository->get($id);
        $this->gatekeeper->mayI('delete', $user)->please();
        $user->delete();
        return $user;
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function getUser($id)
    {
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
        return (bool)$this->userRepository->deleteByIdWithEmailConfirmation($user, $email);
    }

    /**
     * @param int $user
     *
     * @return bool
     */
    public function reactivateUser($user)
    {
        return (bool)$this->userRepository->reactivateById($user);
    }

    /**
     * @param  int $user
     *
     * @return bool
     */
    public function getUserNicknameSetting($user)
    {
        return (bool)$this->userRepository->getByIdWithSettings($user)->settings->use_nickname;
    }

    /**
     * @param int  $user
     * @param bool $useNickname
     *
     * @return bool
     */
    public function setUserNicknameSetting($user, $useNickname)
    {
        return (bool)$this->userRepository->setUserNicknameSettingById($user, $useNickname);
    }

} 