<?php  namespace Giraffe\Images;

use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Service;
use Giraffe\Users\UserModel;
use Giraffe\Users\UserService;
use Hash;
use Illuminate\Support\Facades\File;
use League\Flysystem\Filesystem;
use Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

class ImageService extends Service
{

    /**
     * @var \Giraffe\Images\ImageRepository
     */
    private $imageRepository;

    private $valid_exts = array('jpeg', 'jpg', 'png', 'gif');
    private $max_size = 5000000;
    private $max_res = 400, $thumb_res = 100;

    public function __construct(
        ImageRepository $imageRepository
    ) {
        parent::__construct();
        $this->imageRepository = $imageRepository;
    }

    public function setMaxSizeLimit($limit)
    {
        $this->max_size = $limit;
    }

    /**
     * @param UserModel $user
     * @param UploadedFile $file
     * @param ImageTypeModel $image_type
     *
     * @throws InvalidCreationException
     * @return ImageModel
     */
    public function createImage($user, $file, $image_type)
    {
        $this->gatekeeper->mayI('create', 'image')->please();

        // Check that image is valid
        $ext = $file->guessClientExtension();
        if($ext == "jpeg")
            $ext = "jpg";
        $size = $file->getClientSize();

        if (!in_array($ext, $this->valid_exts))
            throw new InvalidCreationException('File format unacceptable: ' . $ext);
        if (!$size || $size > $this->max_size)
            throw new InvalidCreationException('File is too large: ' . strval($size));

        // Delete previous unique image if need be
        if ($image_type->unique_per_user) {
            try {
                $previous_image = $this->imageRepository->getByUserWithType($user, $image_type)->first();
                $this->deleteImage($previous_image->hash);
            } catch(NotFoundModelException $e) {
                //To be expected if there is no previous
            }
        }

        // Create data for object
        $data = [];
        $data['user_id'] = $user->id;
        $data['hash'] = Str::random(18);
        $data['extension'] = $ext;
        $data['image_type_id'] = $image_type->id;

        // Create the object
        $image = $this->imageRepository->create($data);
        $this->log->info('New image created', $image->toArray());


        // Create the path for the image file
        $img_dir = public_path()."/images/".$user->hash;
        if (!File::exists($img_dir))
            File::makeDirectory($img_dir, 0775);

        // Create the full image
        $img = Image::make($file);
        if($img->width() > $this->max_res OR $img->height() > $this->max_res)
            $img->fit($this->max_res, $this->max_res);

        $img->save($this->image_path($img_dir, $image));
        $big = $img->getEncoded();

        // Create the thumbnail image
        $thumb = Image::make($file)->fit($this->thumb_res, $this->thumb_res)->save($this->thumb_path($img_dir, $image));
        $small = $thumb->getEncoded();

        $storageMedium = $this->getImageStorageMedium();
        $storageMedium->put($data['hash'] . '.' . $data['extension'], $big);
        $storageMedium->put($data['hash'] . '_thumb.' . $data['extension'], $small);

        return $image;
    }

    /**
     * @param string $hash
     *
     * @return \Giraffe\Images\ImageModel
     */
    public function deleteImage($hash)
    {
        /** @var ImageModel $image */
        $image = $this->imageRepository->getByHash($hash);
        $this->gatekeeper->mayI('delete', $image)->please();
        $img_dir = public_path()."/images/".$image->user()->first()->hash;
        $this->imageRepository->deleteByHash($hash);
        File::delete($this->image_path($img_dir, $image));
        File::delete($this->thumb_path($img_dir, $image));

        $medium = $this->getImageStorageMedium();
        $medium->delete($image->hash . '.' . $image->extension);
        $medium->delete($image->hash . '_thumb.' . $image->extension);

        return $image;
    }

    /**
     * @param string $name
     *
     * @return \Giraffe\Images\ImageTypeModel
     */
    public function getImageType($name) {
        return $this->imageRepository->getImageTypeByName($name);
    }

    /**
     * @param string $img_dir
     * @param ImageModel $image
     *
     * @return string
     */
    private function image_path($img_dir, $image) {
        return $img_dir."/".$image->hash.".".$image->extension;
    }

    /**
     * @param string $img_dir
     * @param ImageModel $image
     *
     * @return string
     */
    private function thumb_path($img_dir, $image) {
        return $img_dir."/".$image->hash."_thumb.".$image->extension;
    }

    /**
     * @return Filesystem
     */
    protected function getImageStorageMedium()
    {
        /** @var Callable $imageFS */
        $imageFS = \Config::get('images.medium');
        /** @var Filesystem $image */
        $image = $imageFS();
        return $image;
    }
}