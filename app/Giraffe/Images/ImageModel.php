<?php namespace Giraffe\Images;

use Carbon\Carbon;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Users\UserModel;

/**
 * Class ImageModel
 *
 * @property int $id
 * @property string $user_id
 * @property string $hash
 * @property string $extension
 * @property int $image_type_id
 */
class ImageModel extends Eloquent implements ProtectedResource {

    protected $softDelete = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id', 'user_id', 'image_type_id', 'created_at', 'updated_at'];

    protected $fillable = ['user_id', 'hash', 'extension'];


    public function user()
    {
        return $this->hasOne('Giraffe\Users\UserModel', 'user_id');
    }

    public function imageType()
    {
        return $this->hasOne('Giraffe\Images\ImageTypeModel', 'image_type_id');
    }


    public function dir() {
        return public_path()."/images/".user()->hash;
    }

    public function path() {
        return $this->dir()."/".$this->hash.".".$this->extension;
    }

    public function thumb_path() {
        return $this->dir()."/".$this->hash."_thumb.".$this->extension;
    }

    /**
     * ---------------- Protected Resource ----------------
     */

    public function getResourceName()
    {
        return "image";
    }

    /**
     * @param \Giraffe\Users\UserModel $user
     *
     * @return bool
     */
    public function checkOwnership(UserModel $user)
    {
        return $this->user_id == $user->id;
    }
}
