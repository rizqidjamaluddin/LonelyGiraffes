<?php  namespace Giraffe\Users;

use Giraffe\Common\DuplicateCreationException;
use Giraffe\Common\DuplicateUpdateException;
use Giraffe\Common\Service;
use Hash;
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
        $data = array_only($data, ['name', 'password', 'email', 'gender']);
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
     * @throws \Giraffe\Common\DuplicateCreationException
     * @return UserModel|null $userModel
     */
    public function updateUser($user_id, $attributes)
    {

        $attributes = array_only($attributes, ['name', 'email', 'gender', 'password']);

        $user = $this->userRepository->get($user_id);
        $this->gatekeeper->mayI('update', $user)->please();

        $this->updateValidator->validate($attributes);

        if (array_key_exists('password', $attributes)) {
            $user->password = Hash::make($attributes['password']);
        }
        try {
            $this->userRepository->update($user, $attributes);
        } catch (DuplicateUpdateException $e) {
            throw new DuplicateCreationException('Another user is using this email.');
        }
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
     * @return UserModel
     */
    public function getUser($id)
    {
        return $this->userRepository->get($id);
    }

    /**
     * @param $email
     *
     * @return UserModel
     */
    public function getUserByEmail($email)
    {
        return $this->userRepository->getByEmail($email);
    }

    /**
     * @param $name
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersByName($name)
    {
        return $this->userRepository->findByName($name);
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

    /**
     * @param string $user
     * @return bool
     */
    public function promoteToAdmin($user)
    {
        $model = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI("promote", $model)->please();
        $this->setUserRole($model, 'admin');
        $this->log->notice($this, "User {$model->email} promoted to administrator.");
        return true;
    }

    /**
     * @param string $user
     * @return bool
     */
    public function demoteToMember($user)
    {
        $model = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI("promote", $model)->please();
        $this->setUserRole($model, 'member');
        $this->log->notice($this, "User {$model->email} demoted to member.");
        return true;
    }

    protected function setUserRole($user_hash, $role)
    {
        $this->userRepository->update($user_hash, ['role' => $role]);
        return true;
    }

} 