<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\UseCases;

use BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports\AuthTokenIssuer;
use http\Exception\InvalidArgumentException;

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
}