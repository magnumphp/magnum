<?php

namespace Magnum\Container;

use Magnum\Container\Stub\BadConstructorC;
use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\ConstructorC;
use Magnum\Container\Stub\StubProvider;
use Magnum\Container\Stub\StubProviderWithSubProvider;
use Magnum\Fixture\TestProxy;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Tests\Compiler\ExtensionCompilerPassTest;
use Symfony\Component\Finder\Finder;

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
		$def->setPublic(true);
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
		$def->setPublic(true);

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
		$def->setPublic(true);
		$builder->alias(ConstructorA::class, $alias);

		$container = $builder->container();
		$obj       = $container->get($alias);

		self::assertInstanceOf(ConstructorA::class, $obj);
	}

	public function testFactory()
	{
		$builder = new Builder();

		$def = $builder->factory('fact', ConstructorA::class, 'test');
		$def->setPublic(true);

		$obj = $builder->container()->get('fact');
		self::assertInstanceOf(ConstructorA::class, $obj);
	}

	public function testSaveToFile()
	{
		vfsStream::setup('test');

		$builder = new Builder();
		$def = $builder->instance(ConstructorA::class);
		$def->setPublic(true);

		$builder->saveToFile("vfs://test/test.php", 'CompiledContainer');
		$content = file_get_contents("vfs://test/test.php");
		self::assertContains("class CompiledContainer", $content);
	}

	public function testAutowiringWorks()
	{
		$builder = new Builder();
		$def = $builder->instance(ConstructorB::class);
		$def->setPublic(true);

		$obj = $builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testFindsClassesInPath()
	{
		$stubPath = __DIR__ . '/Stub';
		$builder = new Builder();

		$classes = [];
		foreach (new \GlobIterator("$stubPath/*.php") as $file) {
			$classes[] = __NAMESPACE__ . "\\Stub\\" . $file->getBaseName('.php');
		}
		self::assertEquals($classes, $builder->findClassesInPath($stubPath));
	}

	public function testBuilderReturnsContainerBuilderInstance()
	{
		self::assertInstanceOf(ContainerBuilder::class, (new Builder())->builder());
	}

	public function testAddCompilerPass()
	{
		$pass = new Stub\DummyCompilerPass();

		$builder = new Builder();
		$builder->addCompilerPass($pass);

		$container = $builder->container();

		self::assertTrue($pass->called);
	}

	public function testGetReturnsDefinition()
	{
		$builder = new Builder();
		$builder->instance(ConstructorB::class);
		$def = $builder->get(ConstructorB::class);

		self::assertInstanceOf(Definition::class, $def);
	}

	public function testSetParameters()
	{
		$builder = new Builder();
		$builder->setParameters(['test' => 'param']);

		/** @var Container $container */
		$container = $builder->container();
		self::assertEquals('param', $container->getParameter('test'));
	}

	public function testSetParameter()
	{
		$builder = new Builder();
		$builder->setParameter('test', 'param');

		/** @var Container $container */
		$container = $builder->container();
		self::assertEquals('param', $container->getParameter('test'));
	}

	public function testHasParameterReturnsFalse()
	{
		$builder = new Builder();

		self::assertFalse($builder->hasParameter('test'));
	}

	public function testHasParameterReturnsTrue()
	{
		$builder = new Builder();
		$builder->setParameter('test', 'param');

		self::assertTrue($builder->hasParameter('test'));
	}

	public function testGetParameterReturnsValue()
	{
		$builder = new Builder();
		$builder->setParameter('test', 'param');

		self::assertEquals('param', $builder->getParameter('test'));
	}

	public function testGetParameterReturnsDefaultWhenNotFound()
	{
		$builder = new Builder();

		self::assertEquals('bowl', $builder->getParameter('test', 'bowl'));
	}

	public function testSetParameterDefault()
	{
		$builder = new Builder();
		$builder->setParameterDefault('test_param', 'param_test');

		$def = $builder->instance(ConstructorA::class);
		$def->setArgument('$a', '%test_param%');
		$def->setPublic(true);

		$container = $builder->container();
		$obj       = $container->get(ConstructorA::class);

		self::assertEquals('param_test', $obj->a);
	}

	public function testProxy()
	{
		$builder = new Builder();
		$builder->proxy('TestProxy', TestProxy::class);

		self::assertEquals(['TestProxy' => TestProxy::class], $builder->container()->getParameter('proxies'));
	}
}