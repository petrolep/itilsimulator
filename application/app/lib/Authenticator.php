<?php

namespace ITILSimulator\Base;

use ITILSimulator\Services\UserService;
use Nette\Object;
use Nette\Security,
	Nette\Utils\Strings;

/**
 * Nette users authenticator, uses UserService to authenticate users.
 */
class Authenticator extends Object implements Security\IAuthenticator
{
	/** @var UserService */
	private $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	/**
	 * Performs an authentication.
	 * @param array $credentials
	 * @return Security\Identity|Security\IIdentity|null
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$userIdentity = $this->userService->createUserIdentity($username, $password);
		if (!$userIdentity) {
			throw new \Nette\Security\AuthenticationException('Invalid credentials.', self::INVALID_CREDENTIAL);
		}

		return $userIdentity;
	}
}
