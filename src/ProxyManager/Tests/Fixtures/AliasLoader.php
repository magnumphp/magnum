<?php

namespace Magnum\ProxyManager\Tests\Fixture;

class AliasLoader
	extends \XStatic\AliasLoader
{
	protected $registered = false;

	public function register($rootNamespace = false)
	{
		$this->registered = true;

		return true;
	}

	public function isRegistered()
	{
		return $this->registered;
	}
}