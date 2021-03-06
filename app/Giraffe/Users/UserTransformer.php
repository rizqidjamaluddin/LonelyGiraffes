<?php  namespace Giraffe\Users;
use Giraffe\Authorization\Gatekeeper;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Images\ImageTransformer;

class UserTransformer extends Transformer
{
    public function getServedClass()
    {
        return UserModel::class;
    }

    /**
     * @param UserModel $userModel
     * @return array
     */
    public function transform($userModel)
    {
        $actingUser = \App::make(Gatekeeper::class)->me();
        $optionals = [];

        if ($userModel->gender)
            $optionals['gender'] = $userModel->gender;

        if ($actingUser && $userModel->hash == $actingUser->hash) {
            $optionals['tutorial_flag'] = $userModel->tutorial_flag;
            $optionals['email'] = $userModel->email;
        }

        $pic = $userModel->profilePic();
        if ($pic) {
            $transformer = new ImageTransformer();
            $pic = $transformer->transform($pic);
            // This is a hackish way of converting an array to an object
            $optionals['pic'] = json_decode (json_encode ($pic), FALSE);;
        }

        return array_merge([
            'hash' => $userModel->hash,
            'name' => $userModel->name,
            'city' => $userModel->city,
            'state' => $userModel->state,
            'country' => $userModel->country,
            'href' => $this->buildUrl($userModel->hash),
            'relationships' => $userModel->getUserRelationships($actingUser)
        ], $optionals);
    }

    protected function buildUrl($hash)
    {
        return url('api/users/'.$hash);
    }

} 
