<?php

use Giraffe\Common\Controller;
use Giraffe\Images\ImageModel;
use Giraffe\Images\ImageService;
use Giraffe\Images\ImageTransformer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImageController extends Controller {

    /**
     * @var \Giraffe\Images\ImageService
     */
    private $imageService;

    /**
     * @param \Giraffe\Images\ImageService $imageService
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
        parent::__construct();
    }

    public function create()
    {
        if (!Input::hasFile('image') || !Input::has('type')) {
            throw new BadRequestHttpException();
        }

        $image_type = $this->imageService->getImageType(Input::get('type'));

        $user = $this->gatekeeper->me();
        $file = Input::file('image');

        $model = $this->imageService->createImage($user, $file, $image_type);
        $model = $this->returnImageModel($model);
        return $model;
    }

    public function delete($img_hash)
    {
        $model = $this->imageService->deleteImage($img_hash);
        return $this->returnImageModel($model);
    }

    /**
     * @param ImageModel $model
     *
     * @return \Illuminate\Http\Response
     */
    public function returnImageModel(ImageModel $model)
    {
        return $this->withItem($model, new ImageTransformer(), 'images');
    }
}