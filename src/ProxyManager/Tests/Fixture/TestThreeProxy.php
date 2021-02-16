<?php

namespace Magnum\ProxyManager\Tests\Fixture;

use Magnum\ProxyManager\StaticProxy;

class TestThreeProxy
	extends StaticProxy
{
	public static function getInstanceIdentifier(): string
	{
		return BadProxy::class;
	}
}