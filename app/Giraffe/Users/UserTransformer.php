<?php  namespace Giraffe\Users;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    public function transform(UserModel $userModel)
    {
        return [
            'hash' => $userModel->hash,
            'name' => $userModel->name,
            'gender' => $userModel->gender,
            'email' => $userModel->email
        ];
    }

} 