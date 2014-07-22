<?php  namespace Giraffe\Images;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{

    public function transform($imageModel)
    {
        return [
            'hash' => $imageModel->hash,
            'extension' => $imageModel->extension,
            'type' => $imageModel->imageType()->first()->name,
            'href' => $this->build_url($imageModel),
            'href_thumb' => $this->build_thumb_url($imageModel),
        ];
    }

    /**
     * @param ImageModel $imageModel
     * @return string
     */
    public function build_url($imageModel) {
        return url("/images/".$imageModel->user()->first()->hash."/".$imageModel->hash.".".$imageModel->extension);
    }

    /**
     * @param ImageModel $imageModel
     * @return string
     */
    public function build_thumb_url($imageModel) {
        return url("/images/".$imageModel->user()->first()->hash."/".$imageModel->hash."_thumb.".$imageModel->extension);
    }

} 