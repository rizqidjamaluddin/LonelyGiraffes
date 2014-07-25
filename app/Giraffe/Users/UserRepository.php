<?php  namespace Giraffe\Users;

use Eloquent;
use Giraffe\Common\DuplicateUpdateException;
use Giraffe\Common\EloquentRepository;
use Giraffe\Common\InvalidUpdateException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchableRepository;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserSettingModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class UserRepository extends EloquentRepository implements TwoDegreeCellSearchableRepository
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

    public function update($identifier, Array $attributes)
    {
        $identifier = $this->flushForUser($identifier);
        return parent::update($identifier, $attributes);
    }

    public function delete($identifier)
    {
        $identifier = $this->flushForUser($identifier);
        return parent::delete($identifier);
    }

    public function getById($id)
    {
        if (!$model = $this->model->where('id', $id)->remember(100)->cacheTags(['user:'.$id])->first()) {
            throw new NotFoundModelException();
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
        if ($hash instanceof UserModel) {
            return $hash;
        }

        if (!$model = $this->model->where('hash', '=', $hash)->remember(100)->cacheTags(['user:'.$hash])->first()) {
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
        if ($name instanceof UserModel) {
            return $name;
        }

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

    public function TwoDegreeCellSearch(array $cell, $options = [])
    {
        $limit = array_key_exists('limit', $options) ? $options['limit'] : 10;
        $skip = array_key_exists('skip', $options) ? $options['skip'] : 0;

        if (array_key_exists('exclude', $options)) {
            $excludes = is_array($options['exclude']) ? $options['exclude'] : [$options['exclude']];
            return $this->model->whereIn('cell', $cell)->whereNotIn('id', $excludes)->take($limit)->get();
        } else {
            return $this->model->whereIn('cell', $cell)->take($limit)->get();
        }
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function flushForUser($identifier)
    {
        $identifier = $this->get($identifier);
        $this->getCache()->tags(['user:' . $identifier->hash])->flush();
        $this->getCache()->tags(['user:' . $identifier->id])->flush();
        return $identifier;
    }
}