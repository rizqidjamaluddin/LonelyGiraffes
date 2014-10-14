<?php  namespace Giraffe\Users; 

use Giraffe\Support\Transformer\Transformer;
use League\Fractal\TransformerAbstract;

class ProfileTransformer extends Transformer
{
    /**
     * @param UserProfileModel $profile
     * @return array
     */
    public function transform($profile)
    {
        return [
            'bio' => $profile->bio ?: '',
            'html_bio' => $profile->html_bio ?: ''
        ];
    }
} 