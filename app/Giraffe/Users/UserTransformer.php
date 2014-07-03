<?php  namespace Giraffe\Users;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    public function transform($userModel)
    {
        if(get_class($userModel)=='Illuminate\Database\Eloquent\Collection') {
            return $userModel->map(function($model) { return $this->transform($model); })->toArray();
        }

        return [
            'hash' => $userModel->hash,
            'name' => $userModel->name,
            'gender' => $userModel->gender,
            'email' => $userModel->email
        ];
    }

} 