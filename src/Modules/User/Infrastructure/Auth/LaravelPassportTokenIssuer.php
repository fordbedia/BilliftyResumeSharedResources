<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use DomainException;
use Illuminate\Support\Facades\Hash;

class LaravelPassportTokenIssuer implements AuthTokenIssuer
{

	public function issueToken(string $email, string $password): array
	{
		$normalizedEmail = mb_strtolower(trim($email));

		/** @var User|null $user */
		$user = User::query()
			->whereRaw('LOWER(email) = ?', [$normalizedEmail])
			->first();

		if (!$user || !Hash::check($password, (string) $user->password)) {
			throw new DomainException('Invalid credentials.');
		}

		if (Hash::needsRehash((string) $user->password)) {
			$user->forceFill([
				'password' => Hash::make($password),
			])->save();
		}

		// Passport Personal Access Token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->token;

		return [
            'token_type'   => 'Bearer',
            'access_token' => $tokenResult->accessToken,
            'expires_at'   => optional($token->expires_at)->toISOString(),
            'user'         => [
                'id'    => $user->id,
                'email' => $user->email,
                'name'  => $user->name,
				'plan' 	=> $user->plan
            ],
        ];
	}
}
