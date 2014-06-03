<?php namespace Giraffe\Users;

use Eloquent;
use Giraffe\Support\AutoInstantiate;
use Giraffe\Common\HasEloquentHash;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

/**
 * Class UserModel
 *
 * @property $id int
 * @property $setting UserSettingModel
 * @property $country string
 * @property $state string
 * @property $city string
 */
class UserModel extends Eloquent implements UserInterface, RemindableInterface {

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
	protected $hidden = array('password');

    protected $fillable = ['public_id', 'nickname', 'firstname', 'lastname', 'email', 'password', 'token', 'cell',
        'country', 'state', 'city', 'lat', 'long',
        'date_of_birth', 'gender'];

    public function setting()
    {
        return $this->hasOne('Giraffe\Users\UserSettingModel', 'user_id');
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
        // TODO: Implement getRememberToken() method.
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
        // TODO: Implement setRememberToken() method.
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        // TODO: Implement getRememberTokenName() method.
    }}