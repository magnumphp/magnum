<?php

namespace Magnum\ProxyManager\Tests;

use Magnum\Container\Builder;
use Magnum\ProxyManager\Proxy;
use Magnum\ProxyManager\StaticProxy;
use Magnum\ProxyManager\Tests\Fixture\BadProxy;
use Magnum\ProxyManager\Tests\Fixture\ProxyClass;
use Magnum\ProxyManager\Tests\Fixture\TestProxy;
use Magnum\ProxyManager\Tests\Fixture\TestTwoProxy;
use Magnum\ProxyManager\Tests\Fixture\TestThreeProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class StaticProxyTest
	extends TestCase
{
	public function testSetContainerKnowsBuilder()
	{
		$b = new Builder();

		StaticProxy::setContainer($b);

		$class      = new \ReflectionClass(StaticProxy::class);
		$properties = $class->getStaticProperties();
		self::assertSame($b->container(), $properties['container']);
	}

	public function testSetContainerThrowsException()
	{
		$this->expectException(\TypeError::class);

		StaticProxy::setContainer(new \stdClass());
	}

	public function testGetInstanceThrowsExceptionWithoutContainerAndInstance()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The Proxy Subject cannot be retrieved because the Container is not set.');

		TestProxy::clear();
		TestProxy::getInstance();
	}

	public function testGetInstanceReturnsFromContainer()
	{
		TestProxy::clear();
		TestProxy::setContainer($c = new Container());
		$c->set(ProxyClass::class, new \stdClass());

		self::assertInstanceOf(\stdClass::class, TestProxy::getInstance());
	}

	public function xtestGetInstanceIdentifierThrowsException()
	{
		$this->expectException(\BadMethodCallException::class);
		$this->expectExceptionMessage(
			// This typo is in ReStatic
			"TheReStatic\\StaticProxy::getInstanceIdentifier method must be implemented by a subclass."
		);

		BadProxy::getInstance();
	}

	public function testMagicMethodsAreProxied()
	{
		TestProxy::clear();
		TestProxy::setInstance($pc = new ProxyClass());
		TestProxy::test();

		self::assertTrue($pc->test);
	}

	public function testInstancesAreSeparate()
	{
		$b = new Builder();
		$b->register(ProxyClass::class)->setPublic(true);
		$b->register(BadProxy::class)->setPublic(true);
		StaticProxy::setContainer($b->container());

		$t2 = TestTwoProxy::getInstance();
		$t3 = TestThreeProxy::getInstance();

		self::assertNotEquals($t2, $t3);
	}
}