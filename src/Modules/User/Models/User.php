<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Models;


use BilliftyResumeSDK\SharedResources\Modules\User\Domain\Authorization\UserEntitlementService;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
	use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';
	protected $guarded = [];
	protected $hidden = [
		'password',
		'remember_token',
	];

	public function info()
	{
		return $this->hasOne(UserInfo::class);
	}

	public static function relationships(): array
	{
		return [
			'info'
		];
	}

	public function userCan(string $ability): bool
	{
		return app(UserEntitlementService::class)->userCan($this, $ability);
	}

	public function allowedTemplatePlans(): array
	{
		return app(UserEntitlementService::class)->allowedTemplatePlans($this);
	}
}
