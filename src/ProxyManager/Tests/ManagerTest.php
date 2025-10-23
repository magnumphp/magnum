<?php

namespace Magnum\ProxyManager\Tests;

use Magnum\ProxyManager\Manager;
use Magnum\ProxyManager\Proxy;
use Magnum\ProxyManager\StaticProxy;
use Magnum\ProxyManager\Tests\Fixture\AliasLoader;
use Magnum\ProxyManager\Tests\Fixture\ProxyClass;
use Magnum\ProxyManager\Tests\Fixture\TestProxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ManagerTest
	extends TestCase
{
	protected Manager $manager;
	protected Container $container;
	protected AliasLoader $aliasLoader;

	public function setUp(): void
	{
		// remove ALL the alias loaders
		foreach (spl_autoload_functions() as $autoload) {
			if ($autoload instanceof \ReStatic\AliasLoader || (is_array($autoload) && $autoload[0] instanceof \ReStatic\AliasLoader)) {
				spl_autoload_unregister($autoload);
			}
		}

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

	public function testAddProxyHonorsCustomProxyClass()
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
		$al = $this->createMock(\ReStatic\AliasLoader::class);
		$al->method('isRegistered')
		   ->willReturnMap([
				   [false],
				   [true]
			   ]);
		self::assertTrue($this->manager->enable());
	}

	public function testEnableDoesNotDoubleRegister()
	{
		$al = $this->createMock(\ReStatic\AliasLoader::class);
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