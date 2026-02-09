<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Infrastructure\Auth;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use DomainException;
use Illuminate\Support\Facades\Auth;

class LaravelPassportTokenIssuer implements AuthTokenIssuer
{

	public function issueToken(string $email, string $password): array
	{
		if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new DomainException('Invalid credentials.');
        }
		$user = Auth::user();

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