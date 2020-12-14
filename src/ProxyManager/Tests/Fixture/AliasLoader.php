<?php

namespace Magnum\ProxyManager\Tests\Fixture;

class AliasLoader
	extends \ReStatic\AliasLoader
{
	protected $registered = false;

	public function register($rootNamespace = false): bool
	{
		$this->registered = true;

		return true;
	}

	public function isRegistered(): bool
	{
		return $this->registered;
	}
}