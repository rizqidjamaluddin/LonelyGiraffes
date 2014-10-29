<?php namespace Giraffe\Users;

use Carbon\Carbon;
use Dingo\Api\Transformer\TransformableInterface;
use Eloquent;
use Giraffe\Authorization\ProtectedResource;
use Giraffe\Buddies\BuddyRepository;
use Giraffe\Buddies\BuddyService;
use Giraffe\Buddies\Requests\BuddyRequestService;
use Giraffe\Common\HasEloquentHash;
use Giraffe\Common\NotFoundModelException;
use Giraffe\Support\Transformer\DefaultTransformable;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Geolocation\Locatable;
use Giraffe\Geolocation\Location;
use Giraffe\Geolocation\UnlocatableModelException;
use Giraffe\Images\ImageTypeModel;
use Giraffe\Support\Transformer\Transformer;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Support\Collection;

/**
 * Class UserModel
 *
 * @property int    $id
 * @property string $hash
 * @property string $name
 * @property string $password
 * @property string $email
 * @property Carbon $date_of_birth
 * @property string $gender
 * @property string $city
 * @property string $state
 * @property string $country
 * @property int    $tutorial_flag
 */
class UserModel extends Eloquent implements UserInterface, Locatable,
    RemindableInterface, ProtectedResource, Transformable, DefaultTransformable {

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

    protected $fillable = [
        'hash',
        'name',
        'email',
        'password',
        'token',
        'cell',
        'country',
        'state',
        'city',
        'lat',
        'long',
        'date_of_birth',
        'gender',
        'role',
        'tutorial_flag'
    ];

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
     * Gets an array of the relationships this user has with another user.
     *
     * @param UserModel $user
     * @return array
     */
    public function getUserRelationships($user)
    {
        if (!$user instanceof UserModel) {
            return [];
        }

        /** @var BuddyService $buddyService */
        $buddyService = \App::make(BuddyService::class);

        /** @var BuddyRequestService $buddyRequestService */
        $buddyRequestService = \App::make(BuddyRequestService::class);

        $rel = [];

        // check for 'self'
        if ($user->id == $this->id) {
            $rel[] = 'self';
            return $rel;
        }

        // check for 'buddy'
        if ($buddyService->checkBuddies($this, $user)) {
            $rel[] = 'buddy';
            return $rel;
        }

        // check for 'outgoing' and 'pending'
        try {
            $request = $buddyRequestService->check($this, $user);
            if ($request->sender()->id == $user->id) {
                $rel[] = 'outgoing';
            } else {
                $rel[] = 'pending';
            }
        } catch (NotFoundModelException $e) {
            // continue
        }

        return $rel;
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
        if ($this->cell) {
            $location->provideCacheMetadata($this->cell);
        }
        return $location;
    }

    /**
     * @return Collection
     */
    public function getBuddies()
    {
        /** @var BuddyRepository $buddyRepository */
        $buddyRepository = \App::make(BuddyRepository::class);
        return $buddyRepository->getByUser($this);
    }

    public function receivedBuddyRequests()
    {
        return $this->belongsTo('Giraffe\Buddies\BuddyRequests\BuddyRequestModel', 'to_user_id');
    }

    public function sentBuddyRequests()
    {
        return $this->hasMany('Giraffe\Buddies\BuddyRequests\BuddyRequestModel', 'from_user_id');
    }

    public function images()
    {
        return $this->hasMany('Giraffe\Images\ImageModel', 'user_id');
    }

    public function profilePic()
    {
        $profile_pic = ImageTypeModel::where('name', '=', 'profile_pic')->first();;
        return $this->images()->where('image_type_id', '=', $profile_pic->id)->first();
    }

    public function enableTutorialFlag()
    {
        $this->tutorial_flag = 1;
    }

    public function disableTutorialFlag()
    {
        $this->tutorial_flag = 0;
    }

    /**
     * @return Transformer
     */
    public function getDefaultTransformer()
    {
        return new UserTransformer();
    }
}
