<?php namespace Giraffe\Models;

use Eloquent;

class UserSettingModel extends Eloquent {
    protected $table = 'user_settings';
	protected $fillable = ['user_id', 'tos_agreement', 'use_nickname'];
}