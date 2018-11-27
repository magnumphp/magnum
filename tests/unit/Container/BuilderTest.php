<?php

namespace Magnum\Container;

use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuilderTest
	extends TestCase
{
	public function testLoads()
	{
		$builder = new Builder();
		self::assertInstanceOf(
			ContainerBuilder::class,
			$builder->container()
		);
	}

	public function testSingleton()
	{
		$builder = new Builder();

		$def = $builder->singleton(ConstructorA::class);
		$def->setArgument('$a', 'test');

		$container = $builder->container();
		$obj       = $container->get(ConstructorA::class);

		self::assertInstanceOf(ConstructorA::class, $obj);
		self::assertEquals('test', $obj->a);
		self::assertTrue($def->isPublic());
		self::assertSame($obj, $container->get(ConstructorA::class));
	}

	public function testInstance()
	{
		$builder = new Builder();

		$def = $builder->instance(ConstructorA::class);
		$def->setArgument('$a', 'test');

		$container = $builder->container();
		$obj       = $container->get(ConstructorA::class);

		self::assertInstanceOf(ConstructorA::class, $obj);
		self::assertEquals('test', $obj->a);
		self::assertTrue($def->isPublic());
		self::assertNotSame($obj, $container->get(ConstructorA::class));
	}

	public function testAlias()
	{
		$builder = new Builder();
		$alias   = 'MyTest';
		$def     = $builder->instance(ConstructorA::class);
		$builder->alias(ConstructorA::class, $alias);

		$container = $builder->container();
		$obj       = $container->get($alias);

		self::assertInstanceOf(ConstructorA::class, $obj);
	}

	public function testFactory()
	{
		$builder = new Builder();

		$builder->factory('fact', ConstructorA::class, 'test');

		$obj = $builder->container()->get('fact');
		self::assertInstanceOf(ConstructorA::class, $obj);
	}

	public function testSaveToFile()
	{
		vfsStream::setup('test');

		$builder = new Builder();
		$builder->instance(ConstructorA::class);

		$builder->saveToFile("vfs://test/test.php", 'CompiledContainer');
		$content = file_get_contents("vfs://test/test.php");
		self::assertContains("class CompiledContainer", $content);
	}

	public function testAutowiringWorks()
	{
		$builder = new Builder();
		$builder->instance(ConstructorB::class);

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}


}