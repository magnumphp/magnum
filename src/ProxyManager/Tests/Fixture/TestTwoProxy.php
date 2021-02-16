<?php

namespace Magnum\ProxyManager\Tests\Fixture;

use Magnum\ProxyManager\StaticProxy;

class TestTwoProxy
	extends StaticProxy
{
	public static function getInstanceIdentifier(): string
	{
		return ProxyClass::class;
	}
}