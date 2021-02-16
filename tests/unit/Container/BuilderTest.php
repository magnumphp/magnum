<?php

namespace Magnum\Container;

use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\DecorateA;
use Magnum\ProxyManager\Tests\Fixture\TestProxy;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BuilderTest
	extends TestCase
{
	/**
	 * @var Builder
	 */
	protected $builder;

	public function setUp(): void
	{
		$this->builder = new Builder();
	}

	public function testLoads()
	{
		self::assertInstanceOf(ContainerBuilder::class, $this->builder->container());
	}

	public function testBuilderReturnsContainerBuilderInstance()
	{
		self::assertInstanceOf(ContainerBuilder::class, $this->builder->builder());
	}

	public function testSingleton()
	{
		$this->builder->singleton(ConstructorA::class);

		self::assertTrue($this->builder->builder()->hasDefinition(ConstructorA::class));
	}

	public function testInstance()
	{
		$this->builder->instance(ConstructorA::class);

		self::assertTrue($this->builder->builder()->hasDefinition(ConstructorA::class));
	}

	public function testAlias()
	{
		$this->builder->alias('MyTest', 'test');

		self::assertTrue($this->builder->builder()->hasAlias('test'));
	}

	public function testFactory()
	{
		$this->builder->factory('fact', ConstructorA::class, 'test');

		self::assertTrue($this->builder->builder()->hasDefinition('fact'));
	}

	public function testSaveToFile()
	{
		vfsStream::setup('test');

		$this->builder->saveToFile("vfs://test/test.php", 'CompiledContainer');
		$content = file_get_contents("vfs://test/test.php");
		self::assertStringContainsString("class CompiledContainer", $content);
	}

	public function xtestAutowiringWorks()
	{
		$def = $this->builder->instance(ConstructorB::class);
		$def->setPublic(true);

		$obj = $this->builder->container()->get(ConstructorB::class);
		self::assertInstanceOf(ConstructorA::class, $obj->a);
	}

	public function testFindsClassesInPath()
	{
		$stubPath = __DIR__ . '/Stub';
		$expected = [];
		foreach (new \GlobIterator("$stubPath/*.php") as $file) {
			$expected[] = __NAMESPACE__ . "\\Stub\\" . $file->getBaseName('.php');
		}

		$actual = $this->builder->findClassesInPath($stubPath);
		sort($actual);
		sort($expected);
		self::assertEquals($expected, $actual);
	}

	public function testAddCompilerPass()
	{
		$pass = new Stub\DummyCompilerPass();

		$this->builder->addCompilerPass($pass);

		$this->builder->container();

		self::assertTrue($pass->called);
	}

	public function testGetReturnsDefinition()
	{
		$this->builder->instance(ConstructorB::class);
		$def = $this->builder->get(ConstructorB::class);

		self::assertInstanceOf(Definition::class, $def);
	}

	public function testSetParameters()
	{
		$this->builder->setParameters(['test' => 'param']);

		/** @var Container $container */
		$container = $this->builder->container();
		self::assertEquals('param', $container->getParameter('test'));
	}

	public function testSetParameter()
	{
		$this->builder->setParameter('test', 'param');

		/** @var Container $container */
		$container = $this->builder->container();
		self::assertEquals('param', $container->getParameter('test'));
	}

	public function testHasParameterReturnsFalse()
	{
		self::assertFalse($this->builder->hasParameter('test'));
	}

	public function testHasParameterReturnsTrue()
	{
		$this->builder->setParameter('test', 'param');

		self::assertTrue($this->builder->hasParameter('test'));
	}

	public function testGetParameterReturnsValue()
	{
		$this->builder->setParameter('test', 'param');

		self::assertEquals('param', $this->builder->getParameter('test'));
	}

	public function testGetParameterReturnsDefaultWhenNotFound()
	{
		self::assertEquals('bowl', $this->builder->getParameter('test', 'bowl'));
	}

	public function testSetParameterDefault()
	{
		$this->builder->setParameterDefault('test_param', 'param_test');

		self::assertEquals('param_test', $this->builder->getParameter('test_param'));
	}

	public function testProxy()
	{
		$this->builder->proxy('TestProxy', TestProxy::class);

		self::assertEquals(['TestProxy' => TestProxy::class], $this->builder->container()->getParameter('proxies'));
	}

	public function testModifiers()
	{
		$this->builder->modifier(ConstructorA::class)->setPublic(true)->addMethodCall('modify', ['k']);

		$this->builder->register(ConstructorA::class);

		self::assertTrue($this->builder->container()->get(ConstructorA::class)->modified);
	}

	public function testModifiersFollowAliases()
	{
		$this->builder->modifier('kakaw')->setPublic(true)->addMethodCall('modify', ['k']);

		$this->builder->register(ConstructorA::class);
		$this->builder->alias(ConstructorA::class, 'kakaw');

		$container = $this->builder->container();

		$a = $container->get('kakaw');
		$b = $container->get(ConstructorA::class);
		self::assertSame($a, $b);
	}

	public function testReference()
	{
		self::assertInstanceOf(Reference::class, $ref = $this->builder->reference(ConstructorA::class));
		self::assertInstanceOf(Definition::class, $this->builder->get(ConstructorA::class));
	}

	public function testDecorate()
	{
		$this->builder->register(ConstructorA::class)->setPublic(true);

		$this->builder->decorate(ConstructorA::class, DecorateA::class);
		$this->builder->decorate(ConstructorA::class, DecorateA::class);

		self::assertEmpty($this->builder->get(ConstructorA::class)->getMethodCalls());
		self::assertInstanceOf(DecorateA::class, $this->builder->container()->get(ConstructorA::class));
	}

	public function testDecorateUsesExistingDefinitionForDecorator()
	{
		$this->builder->register(ConstructorA::class)->setPublic(true);
		$this->builder->register(DecorateA::class)->addMethodCall('modify', ['kakaw']);

		$this->builder->decorate(ConstructorA::class, DecorateA::class);

		self::assertNotEmpty($this->builder->get(ConstructorA::class)->getMethodCalls());
		self::assertInstanceOf(DecorateA::class, $this->builder->container()->get(ConstructorA::class));
	}
}