<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, Notifiable;

    protected $table = 'users';
	protected $guarded = [];

	public function info()
	{
		return $this->hasOne(UserInfo::class);
	}
}
