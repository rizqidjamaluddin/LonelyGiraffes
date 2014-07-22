<?php namespace Giraffe\Images;

use Carbon\Carbon;
use Eloquent;

/**
 * Class ImageTypeModel
 *
 * @property int $id
 * @property string $name
 * @property boolean $unique_per_user
 * @property int $image_type_id
 */
class ImageTypeModel extends Eloquent {

    protected $softDelete = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'image_types';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['id', 'created_at', 'updated_at'];

    protected $fillable = ['name', 'unique_per_user'];


    public function images()
    {
        return $this->hasMany('Giraffe\Images\ImageModel', 'image_type_id');
    }

    /**
     * ---------------- Protected Resource ----------------
     */

    public function getResourceName()
    {
        return "image_types";
    }
}
