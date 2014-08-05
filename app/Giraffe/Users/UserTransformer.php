<?php  namespace Giraffe\Users;
use Giraffe\Authorization\Gatekeeper;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    public function transform(UserModel $userModel)
    {

        $actingUser = \App::make(Gatekeeper::class)->me();

        $gender = $userModel->gender ? ['gender' => $userModel->gender] : [];

        return array_merge([
            'hash' => $userModel->hash,
            'name' => $userModel->name,
            'email' => $userModel->email,
            'city' => $userModel->city,
            'state' => $userModel->state,
            'country' => $userModel->country,
            'href' => $this->buildUrl($userModel->hash),
            'relationships' => $userModel->getUserRelationships($actingUser)
        ], $gender);
    }

    protected function buildUrl($hash)
    {
        return url('api/users/'.$hash);
    }

} 