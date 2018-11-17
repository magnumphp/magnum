<?php

namespace Magnum\Container\Stub;

use Magnum\Container\Fixture\ConstructorE;
use Magnum\Container\AbstractContainerConfig;

class TestContainerConfig
	extends AbstractContainerConfig
{
	public function __construct()
	{
		$this->entryPoints = [
			ConstructorE::class
		];
	}
}