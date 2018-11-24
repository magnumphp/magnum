<?php
declare(strict_types=1);

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Config\ParamHint;
use Magnum\Container\Fixture\ConstructorA;
use Magnum\Container\Fixture\ConstructorB;
use Magnum\Container\Fixture\ConstructorC;
use Magnum\Container\Fixture\ConstructorE;
use Magnum\Container\Param\StringParam;
use Magnum\Container\Stub\StubContainerConfig;
use Magnum\Container\Stub\TestContainerConfig;
use Magnum\Container\Stub\TestProvider;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ContainerTest
	extends TestCase
{
	/**
	 * @runInSeparateProcess
	 */
	public function testBuildWithoutCompilation()
	{
		$ep             = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$compilerConfig = $this->createCompilerConfig($ep);
		$compilerConfig->disableCompilation();

		$container = new Container($compilerConfig);
		$c         = $container->build();

		$a = $container->get(ConstructorA::class);
		self::assertInstanceOf(ConstructorA::class, $a);

		// this is an auto-wire
		self::assertFalse($container->has(ConstructorC::class));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWithCompilation()
	{
		// because we need PHP to actually load the container
		$cc = new CompilerConfig("/tmp", TestContainerConfig::class);
		$container = new Container($cc);
		$c = $container->build();

		self::assertInstanceOf('Magnum\Compiled\Container', $c);
		self::assertInstanceOf(ConstructorE::class, $c->get(ConstructorE::class));
		unlink('/tmp/container.php');
	}

	public function testWithParamHint()
	{
		$ep             = new ClassEntryPoint(ConstructorA::class);
		$compilerConfig = $this->createCompilerConfig($ep, ['e' => new ParamHint(new StringParam('test'))]);
		$compilerConfig->disableCompilation();

		$container = new Container($compilerConfig);
		$c         = $container->build();


		$a = $container->get(ConstructorA::class);
		self::assertInstanceOf(ConstructorA::class, $a);
		self::assertEquals('test', $a->e);

		// this is an auto-wire
		self::assertFalse($container->has(ConstructorC::class));
	}

	protected function createCompilerConfig($entryPoint, $definitionHints = [])
	{
		return new CompilerConfig(
			'/tmp',
			new StubContainerConfig([$entryPoint], $definitionHints)
		);
	}
}