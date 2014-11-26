<?php  namespace Giraffe\Users;

use Giraffe\Common\DuplicateCreationException;
use Giraffe\Common\DuplicateUpdateException;
use Giraffe\Common\Service;
use Giraffe\Common\ValidationException;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\NotFoundLocationException;
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
    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(
        UserRepository $userRepository,
        UserCreationValidator $creationValidator,
        UserUpdateValidator $updateValidator,
        LocationService $locationService
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->creationValidator = $creationValidator;
        $this->updateValidator = $updateValidator;
        $this->locationService = $locationService;
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
        $this->log->info('New user registered', $user->toArray());
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

        $attributes = array_only($attributes, ['name', 'email', 'gender', 'password', 'city', 'state', 'country']);

        $user = $this->userRepository->getByHash($user_id);
        $this->gatekeeper->mayI('update', $user)->please();

        $this->updateValidator->validate($attributes);

        // intercept password change
        if (array_key_exists('password', $attributes)) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        // intercept location change
        if ($cacheString = $this->locationService->getCacheStringFromAttributesArray($attributes)) {
            $attributes['cell'] = $cacheString;
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
        $user = $this->userRepository->getByHash($id);
        $this->gatekeeper->mayI('delete', $user)->please();
        $this->userRepository->delete($user);
        return $user;
    }

    /**
     * @param $hash
     *
     * @return UserModel
     */
    public function getUser($hash)
    {
        // if a user model is given, we refresh it to make sure we get the newest one
        if ($hash instanceof UserModel) {
            return $this->userRepository->getByHash($hash->hash);
        } else {
            return $this->userRepository->getByHash($hash);
        }
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
        return $this->userRepository->getByName($name);
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

    public function getNearbyUsers($user)
    {
        /** @var UserModel $user */
        $user = $this->userRepository->getByHash($user);
        return $this->locationService->getNearbyFromRepository($user, $this->userRepository, ['exclude' => $user->id]);
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
        $this->log->notice("User {$model->email} promoted to administrator.");
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
        $this->log->notice("User {$model->email} demoted to member.");
        return true;
    }

    protected function setUserRole($user_hash, $role)
    {
        $this->userRepository->update($user_hash, ['role' => $role]);
        return true;
    }

    public function enableTutorialMode($user)
    {
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('change-tutorial-flag', $user)->please();
        $user->enableTutorialFlag();
        $this->userRepository->save($user);
    }

    public function disableTutorialMode($user)
    {
        $user = $this->userRepository->getByHash($user);
        $this->gatekeeper->mayI('change-tutorial-flag', $user)->please();
        $user->disableTutorialFlag();
        $this->userRepository->save($user);
    }

}