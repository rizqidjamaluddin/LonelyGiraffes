<?php  namespace Giraffe\Users;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserSettingModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends EloquentRepository
{

    /**
     * @var \Giraffe\Users\UserSettingModel
     */
    private $userSettingModel;

    public function __construct(UserModel $userModel, UserSettingModel $userSettingModel)
    {
        parent::__construct($userModel);
        $this->userSettingModel = $userSettingModel;
    }

    /**
     * Extend the base get() method to accept a user's public_id
     *
     * @param \Eloquent|int|string $identifier
     * @return UserModel|null
     */
    public function get($identifier){

        // skip specific search if identifier is null; delegate to parent to decide what to do
        if (is_null($identifier)) {
            return parent::get($identifier);
        }

        // immediately return if already a UserModel
        if ($identifier instanceof UserModel) {
            return $identifier;
        }

        try {
            $model = $this->getByHash($identifier);
        } catch (NotFoundModelException $e) {
            return parent::get($identifier);
        }
        return $model;
    }

    /**
     * @param string $hash
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return UserModel
     */
    public function getByHash($hash)
    {
        if (!$model = $this->model->where('hash', '=', $hash)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * @param string $name
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return UserModel
     */
    public function getByPublicId($name)
    {
        if (!$model = $this->model->where('name', '=', $name)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * @param string $email
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return UserModel
     */
    public function getByEmail($email)
    {
        if ($email instanceof UserModel) {
            return $email;
        }

        if (!$model = $this->model->where('email', '=', $email)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /**
     * @param string $name
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByName($name)
    {
        $models = $this->model->where('name', '=', $name)->get();
        if ($models->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $models;
    }

    /**
     * @param int    $id
     * @param string $email
     *
     * @return bool
     */
    public function deleteByIdWithEmailConfirmation($id, $email)
    {
        return (bool) $this->model->where('id', '=', $id)->where('email', '=', $email)->delete();
    }

    /**
     * @param int    $id 
     * @return bool
     */
    public function reactivateById($id) 
    {
        return (bool) $this->model->where('id', '=', $id)->restore();
    }

    /**
     * @param $id
     *
     * @return Collection|Model|null|static
     */
    public function getByIdWithSettings($id)
    {
        return $this->model->with('setting')->find($id);
    }

    /**
     * @param int  $id
     * @param bool $new_show_nickname_setting
     *
     * @return int|mixed
     */
    public function setUserNicknameSettingById($id, $new_show_nickname_setting)
    {
        return $this->userSettingModel->where('user_id', '=', $id)->update(['use_nickname' => (bool) $new_show_nickname_setting]);
    }

} 