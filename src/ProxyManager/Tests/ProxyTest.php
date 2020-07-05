<?php

namespace Magnum\ProxyManager\Tests;

use Magnum\ProxyManager\Manager;
use Magnum\ProxyManager\Proxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ProxyTest
	extends TestCase
{
	public function setUp(): void
	{
		$this->manager = new Manager(
			$this->container = new Container()
		);
	}

	public function xtestSetInstance()
	{
		Proxy::setInstance($manager = new Manager(new Container()));

		$class      = new \ReflectionClass(Proxy::class);
		$properties = $class->getStaticProperties();
		self::assertSame($manager, $properties['instance']);
	}

	public function testMagicMethodCallsProxy()
	{
		self::assertSame(
			$this->manager,
			Proxy::setContainer(new Container())
		);
	}
}