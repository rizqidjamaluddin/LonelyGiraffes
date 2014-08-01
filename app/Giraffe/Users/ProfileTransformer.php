<?php  namespace Giraffe\Users; 

use League\Fractal\TransformerAbstract;

class ProfileTransformer extends TransformerAbstract
{
    public function transform(UserProfileModel $profile)
    {
        return [
            'bio' => $profile->bio ?: '',
            'html_bio' => $profile->html_bio ?: ''
        ];
    }
} 