<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Application\User\Ports;

interface AuthTokenIssuer
{
	/**
	 * @param string $email
	 * @param string $password
	 * @return array
	 */
	public function issueToken(string $email, string $password): array;
}