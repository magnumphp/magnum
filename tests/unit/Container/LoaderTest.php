<?php

namespace Magnum\Container;

use Magnum\Container\Exception\InvalidProvider;
use Magnum\Container\Stub\BadConstructorC;
use Magnum\Container\Stub\ConstructorA;
use Magnum\Container\Stub\ConstructorB;
use Magnum\Container\Stub\ConstructorC;
use Magnum\Container\Stub\StubProvider;
use Magnum\Container\Stub\StubProviderWithSubProvider;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoaderTest
	extends TestCase
{
	public function testConstruction()
	{
		$vfs = vfsStream::setup('root', null, [
			'container.php' => 'kakaw'
		]);
		$loader = new Loader(false, $vfs->getChild('container.php')->url());

		self::assertTrue($loader->isCompiled());
	}

	public function testLoadReturnsContainer()
	{
		self::assertInstanceOf(ContainerInterface::class, (new Loader)->load());
	}

	public function testLoadSavesCompiledContainer()
	{
		$vfs = vfsStream::setup('root');
		$file = $vfs->url() . '/container.php';
		(new Loader(true, $file))->load();

		self::assertFileExists($file);
	}

	public function testLoadReturnsCompiledContainer()
	{
		$vfs = vfsStream::setup('root', null, [
			'container.php' => '<?php class CompiledContainer implements \Psr\Container\ContainerInterface{
				public function get($id) {}
				public function has($id) {}
			}'
		]);
		$file = $vfs->url() . '/container.php';
		$container = (new Loader(true, $file))->load();

		self::assertInstanceOf('CompiledContainer', $container);
	}

	public function testRegisterAcceptsStringOrProviderInstance()
	{
		$loader = new Loader();

		$loader->register(StubProvider::class);
		$loader->register(new class('a') implements Provider {
			public function register(Builder $builder)
			{}
		});

		$this->expectException(InvalidProvider::class);
		$loader->register('not_a_class');
	}

	public function testLoadRunsProviders()
	{
		$loader = new Loader();
		$loader->register(StubProvider::class);

		self::assertTrue($loader->load()->getParameter('stub'));
	}

	public function testRegisterHandlesSubProviders()
	{
		$loader = new Loader();
		$loader->register(StubProviderWithSubProvider::class);

		self::assertTrue($loader->load()->getParameter('stub'));
		self::assertTrue($loader->load()->getParameter('stub-with-sub'));
	}

	public function testLoadInjectsParameters()
	{
		$loader = new Loader();
		$loader->param('test', 'param');
		$loader->params(['test2' => 'param2']);

		$container = $loader->load();
		self::assertEquals('param', $container->getParameter('test'));
		self::assertEquals('param2', $container->getParameter('test2'));
	}

	public function testParamReturnsInstance()
	{
		$loader = new Loader();
		self::assertSame($loader, $loader->param('test', 'param'));
	}

	public function testParamsReturnsInstance()
	{
		$loader = new Loader();
		self::assertSame($loader, $loader->params(['test' => 'param']));
	}
}