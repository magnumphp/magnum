<?php

namespace Magnum\Container;

use Magnum\Container\Stub\BadConstructorC;
use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\ConstructorC;
use Magnum\Container\Stub\StubProvider;
use Magnum\Container\Stub\StubProviderWithSubProvider;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

	public function testParams()
	{
		$builder = new Builder();
		$builder->params(['test' => 'param']);

		/** @var Container $container */
		$container = $builder->container();
		self::assertEquals('param', $container->getParameter('test'));
	}

	public function testParam()
	{
		$builder = new Builder();
		$builder->param('test', 'param');

		/** @var Container $container */
		$container = $builder->container();
		self::assertEquals('param', $container->getParameter('test'));
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
}