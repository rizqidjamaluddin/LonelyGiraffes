<?php  namespace Giraffe\Users;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    public function transform(UserModel $userModel)
    {
        return [
            'hash' => $userModel->hash,
            'firstname' => $userModel->firstname,
            'lastname' => $userModel->lastname,
            'gender' => $userModel->gender,
            'email' => $userModel->email
        ];
    }

} 