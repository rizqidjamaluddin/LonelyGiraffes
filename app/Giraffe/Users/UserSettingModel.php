<?php namespace Giraffe\Users;

use Eloquent;

/**
 * @property $user_id int
 * @property $tos_agreement bool
 * @property $use_nickname bool
 */
class UserSettingModel extends Eloquent {
    protected $table = 'user_settings';
	protected $fillable = ['user_id', 'tos_agreement', 'use_nickname'];
}