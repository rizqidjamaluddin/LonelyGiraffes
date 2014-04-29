<?php  namespace Giraffe\Repositories; 

use Giraffe\Models\UserModel;
use Giraffe\Models\UserSettingModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseEloquentRepository
{

    /**
     * @var \Giraffe\Models\UserSettingModel
     */
    private $userSettingModel;

    public function __construct(UserModel $userModel, UserSettingModel $userSettingModel)
    {
        parent::__construct($userModel);
        $this->userSettingModel = $userSettingModel;
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