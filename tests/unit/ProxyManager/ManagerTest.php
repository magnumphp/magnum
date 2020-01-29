<?php

namespace Magnum\ProxyManager;

use Magnum\Fixture\AliasLoader;
use Magnum\Fixture\ProxyClass;
use Magnum\Fixture\TestProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ManagerTest
	extends TestCase
{
	public function setUp(): void
	{
		$this->manager = new Manager(
			$this->container = new Container(),
			$this->aliasLoader = new AliasLoader()
		);
	}

	public function testObjectInitializes()
	{
		$class      = new \ReflectionClass(Proxy::class);
		$properties = $class->getStaticProperties();
		self::assertSame($this->manager, $properties['instance']);
	}

	public function testAddProxy()
	{
		$this->manager->addProxy(
			'TestProxy',
			TestProxy::class,
			$pc = new ProxyClass()
		);

		self::assertInstanceOf(
			ProxyClass::class,
			$instance = TestProxy::getInstance()
		);
		self::assertSame($pc, $instance);
	}

	public function testSetContainer()
	{
		$this->manager->setContainer($c = new Container());

		$class      = new \ReflectionClass(StaticProxy::class);
		$properties = $class->getStaticProperties();

		self::assertSame($c, $properties['container']);
	}

	public function testEnableRegistersTheAliasLoader()
	{
		$al = $this->createMock(\XStatic\AliasLoader::class);
		$al->method('isRegistered')
			->will($this->returnValueMap(
				[false],
				[true]
			));
		self::assertTrue($this->manager->enable());
	}

	public function testEnableDoesNotDoubleRegister()
	{
		$al = $this->createMock(\XStatic\AliasLoader::class);
		$al->method('isRegistered')
			->willReturn(true);
		$al->expects($this->never())
		   ->method('register');
		$m = new Manager(
			new Container,
			$al
		);

		self::assertTrue($m->enable());
		self::assertTrue($m->enable());
	}
}