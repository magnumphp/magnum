<?php

namespace Magnum\Container\Stub;

use Magnum\Container\Fixture\ConstructorE;
use Magnum\Container\Provider;

class TestProvider
	extends Provider
{
	public function __construct()
	{
		parent::__construct([
			ConstructorE::class
		]);
	}
}