<?php  namespace Giraffe\Passwords;

use Carbon\Carbon;
use Giraffe\Users\UserModel;
use Str;

/**
 * Class ResetTokenModel
 *
 * @package Giraffe\Passwords
 */
class ResetTokenModel extends \Eloquent
{
    protected $table = 'reset_tokens';
    protected $fillable = ['user_id', 'token', 'expires_at'];

    public static function issue(UserModel $user)
    {
        $i = new static;
        $i->user_id = $user->id;
        $i->token = Str::random(128);
        $i->expires_at = Carbon::now()->addDays(3);
        return $i;
    }
} 