<?php  namespace Giraffe\Images;

use Giraffe\Common\InvalidCreationException;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Service;
use Giraffe\Users\UserService;
use Hash;
use Illuminate\Support\Facades\File;
use Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;

class ImageService extends Service
{

    /**
     * @var \Giraffe\Images\ImageRepository
     */
    private $imageRepository;

    /**
     * @var \Giraffe\Users\UserService
     */
    private $userService;

    private $valid_exts = array('jpeg', 'jpg', 'png', 'gif');
    private $max_size = 10240000; // max file size (1000kb)
    private $max_res = 400, $thumb_res = 100;

    public function __construct(
        ImageRepository $imageRepository,
        UserService $userService
    ) {
        parent::__construct();
        $this->imageRepository = $imageRepository;
        $this->userService = $userService;
    }

    /**
     * @param string $user_hash
     * @param UploadedFile $file
     * @param string $image_type
     *
     * @throws InvalidCreationException
     * @return ImageModel
     */
    public function createImage($user_hash, $file, $image_type)
    {
        //$this->gatekeeper->mayI('create', 'image')->please();

        $image_type = $this->imageRepository->getImageTypeByName($image_type);
        $user = $this->userService->getUser($user_hash);

        $ext = $file->guessClientExtension();
        $size = $file->getClientSize();

        if (!in_array($ext, $this->valid_exts) OR $size > $this->max_size) {
            throw new InvalidCreationException('File format unacceptable or size is too large.');
        }

        // Delete previous unique image if need be
        if ($image_type->unique_per_user) {
            try {
                $previous_image = $this->imageRepository->getByUserWithType($user, $image_type)->first();
                $this->imageRepository->deleteByHash($previous_image->hash);
            } catch(NotFoundModelException $e) {}
        }


        $data = [];
        $data['user_id'] = $user->id;
        $data['hash'] = Str::random(18);
        $data['extension'] = $file->guessClientExtension();
        $data['image_type_id'] = $image_type->id;


        $image = $this->imageRepository->create($data);
        $this->log->info($this, 'New image created', $image->toArray());

        // Create the path for the image
        $img_dir = public_path()."/images/".$user->hash;
        if (!File::exists($img_dir))
            File::makeDirectory($img_dir, 0775);

        // Create the full image
        $img = Image::make($file);
        if($img->width() > $this->max_res OR $img->height() > $this->max_res)
            $img->fit($this->max_res, $this->max_res);
        $img->save($this->image_path($img_dir, $image));

        // Create the thumbnail
        Image::make($file)->fit($this->thumb_res, $this->thumb_res)->save($this->thumb_path($img_dir, $image));
        return $image;
    }

    /**
     * @param string $hash
     *
     * @return \Giraffe\Images\ImageModel|null $imageModel
     */
    public function deleteImage($hash)
    {
        $image = $this->imageRepository->getByHash($hash);
        //$this->gatekeeper->mayI('delete', $image)->please();
        $img_dir = public_path()."/images/".$image->user()->first()->hash;
        $this->imageRepository->deleteByHash($hash);
        File::delete($this->image_path($img_dir, $image));
        File::delete($this->thumb_path($img_dir, $image));
        return $image;
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
}