<?php namespace Giraffe\Users;

use Carbon\Carbon;
use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Geolocation\Locatable;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\UnlocatableModelException;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * Class UserModel
 *
 * @property int $id
 * @property string $hash
 * @property string $name
 * @property string $password
 * @property string $email
 * @property Carbon $date_of_birth
 * @property string $gender
 * @property string $city
 * @property string $state
 * @property string $country
 */
class UserModel extends Eloquent implements UserInterface, Locatable,
    RemindableInterface, ProtectedResource, TransformableInterface {

    use HasEloquentHash;

    protected $softDelete = true;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['id', 'password', 'created_at'];

    protected $fillable = ['hash', 'name', 'email', 'password', 'token', 'cell',
        'country', 'state', 'city', 'lat', 'long',
        'date_of_birth', 'gender', 'role'];

    public function getDates()
    {
        return ['date_of_birth', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function setting()
    {
        return $this->hasOne('Giraffe\Users\UserSettingModel', 'user_id');
    }


    /**
     * ---------------- Protected Resource ----------------
     */

    public function getResourceName()
    {
        return "user";
    }

    public function checkOwnership(UserModel $userModel)
    {
        return $this->id == $userModel->id;
    }



    /**
     * ---------------- Auth ----------------
     */

    /**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        return;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'unused_token';
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new UserTransformer();
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        if (!$this->city || !$this->state || !$this->country) {
            throw new UnlocatableModelException('No user location given');
        }

        $location = new Location();
        $location->provideCity($this->city, $this->state, $this->country);
        if ($this->cell) $location->provideCacheMetadata($this->cell);
        return $location;
    }
    public function receivedBuddyRequests()
    {
        return $this->belongsTo('Giraffe\BuddyRequests\BuddyRequestModel', 'to_user_id');
    }

    public function sentBuddyRequests()
    {
        return $this->hasMany('Giraffe\BuddyRequests\BuddyRequestModel', 'from_user_id');
    }
}
