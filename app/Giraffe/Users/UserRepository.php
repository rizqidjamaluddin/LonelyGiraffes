<?php  namespace Giraffe\Users;

use Giraffe\Common\BaseEloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserSettingModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseEloquentRepository
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
     * @return mixed|void
     */
    public function get($identifier){
        try {
            $model = $this->getByPublicId($identifier);
        } catch (NotFoundModelException $e) {
            return parent::get($identifier);
        }
        return $model;
    }

    /**
     * @param string $id
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getByPublicId($id)
    {
        if (!$model = $this->model->where('public_id', '=', $id)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
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