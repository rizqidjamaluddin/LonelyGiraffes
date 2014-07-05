<?php  namespace Giraffe\Users;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    public function transform($userModel)
    {

        $gender = $userModel->gender ? ['gender' => $userModel->gender] : [];

        return array_merge([
            'hash' => $userModel->hash,
            'name' => $userModel->name,
            'email' => $userModel->email,
            'city' => $userModel->city,
            'state' => $userModel->state,
            'country' => $userModel->country,
            'href' => $this->buildUrl($userModel->hash)
        ], $gender);
    }

    protected function buildUrl($hash)
    {
        return url('api/users/'.$hash);
    }

} 