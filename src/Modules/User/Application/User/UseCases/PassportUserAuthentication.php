<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use BilliftyResumeSDK\SharedResources\Modules\User\Models\User;
use InvalidArgumentException;

class PassportUserAuthentication
{
	public function __construct(private readonly AuthTokenIssuer $tokenIssuer)
	{}

	public function handle(string $email, string $password): array
	{
		$email = trim($email);

		if ($email === '' || $password === '') {
			throw new InvalidArgumentException('Email and password are required');
		}

		return $this->tokenIssuer->issueToken($email, $password);
	}

	public function handleUser(User $user): array
	{
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
				'plan'  => $user->plan,
			],
		];
	}
}
