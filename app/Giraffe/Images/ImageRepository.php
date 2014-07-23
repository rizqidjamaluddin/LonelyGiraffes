<?php  namespace Giraffe\Images;

use Giraffe\Common\EloquentRepository;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Images\ImageTypeModel;
use Giraffe\Users\UserModel;
use Illuminate\Database\Eloquent\Collection;
use Intervention\Image\ImageManagerStatic as Image;

class ImageRepository extends EloquentRepository
{

    /**
     * @var \Giraffe\Images\ImageTypeModel
     */
    private $imageTypeModel;

    public function __construct(ImageModel $imageModel, ImageTypeModel $imageTypeModel)
    {
        parent::__construct($imageModel);
        $this->imageTypeModel = $imageTypeModel;
    }

    /**
     * @param string $hash
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return ImageModel
     */
    public function getByHash($hash)
    {
        if ($hash instanceof ImageModel) {
            return $hash;
        }

        if (!$model = $this->model->where('hash', '=', $hash)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }

    /** Finds all images belonging to a user
     * @param UserModel $user
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUser($user)
    {
        $models = $this->model->where('user_id', '=', $user->id)->get();
        if ($models->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $models;
    }

    /** Finds all images belonging to a user of a certain type
     * @param UserModel $user
     * @param ImageTypeModel $type
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByUserWithType($user, $type)
    {
        $models = $this->model->where('user_id', '=', $user->id)->where('image_type_id', '=', $type->id)->get();
        if ($models->isEmpty()) {
            throw new NotFoundModelException();
        }
        return $models;
    }


    /** Gets an ImageType
     * @param string $name
     *
     * @throws \Giraffe\Common\NotFoundModelException
     * @return \Giraffe\Images\ImageTypeModel
     */
    public function getImageTypeByName($name)
    {
        if ($name instanceof ImageTypeModel) {
            return $name;
        }

        if (!$model = $this->imageTypeModel->where('name', '=', $name)->first()) {
            throw new NotFoundModelException();
        }
        return $model;
    }
}