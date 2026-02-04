<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Services;

use Illuminate\Support\Facades\Hash;

class PasswordHasher
{
	public function hash(string $plain): string
    {
        return Hash::make($plain);
    }

    public function verify(string $plain, string $hash): bool
    {
        return Hash::check($plain, $hash);
    }
}