<?php  namespace Giraffe\Images;
use Config;
use Giraffe\Support\Transformer\Transformer;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends Transformer
{

    /**
     * @param ImageModel $imageModel
     * @return array
     */
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
        return url(Config::get('images.url_prefix') ."/".$imageModel->hash.".".$imageModel->extension);
    }

    /**
     * @param ImageModel $imageModel
     * @return string
     */
    public function build_thumb_url($imageModel) {
        return url(Config::get('images.url_prefix') ."/".$imageModel->hash."_thumb.".$imageModel->extension);

    }

} 