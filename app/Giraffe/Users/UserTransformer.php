<?php  namespace Giraffe\Users;
use League\Fractal\TransformerAbstract;
use Giraffe\Images\ImageTransformer;

class UserTransformer extends TransformerAbstract
{

    public function transform($userModel)
    {
        $optionals = [];

        if ($userModel->gender)
            $optionals['gender'] = $userModel->gender;

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
            'email' => $userModel->email,
            'city' => $userModel->city,
            'state' => $userModel->state,
            'country' => $userModel->country,
            'href' => $this->buildUrl($userModel->hash)
        ], $optionals);
    }

    protected function buildUrl($hash)
    {
        return url('api/users/'.$hash);
    }

} 