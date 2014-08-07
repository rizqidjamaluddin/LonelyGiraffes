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

    protected $fillable = ['user_id', 'image_type_id', 'hash', 'extension'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Giraffe\Users\UserModel', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function imageType()
    {
        return $this->belongsTo('Giraffe\Images\ImageTypeModel', 'image_type_id');
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
