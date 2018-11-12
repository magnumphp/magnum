<?php
declare(strict_types=1);

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Fixture\ConstructorA;
use Magnum\Container\Fixture\ConstructorB;
use Magnum\Container\Fixture\ConstructorC;
use Magnum\Container\Stub\StubContainerConfig;
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
		$ep = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$cc = new CompilerConfig("/tmp", TestProvider::class);
		$container = new Container($cc);
		$c = $container->build();

		self::assertInstanceOf('Magnum\Compiled\Container', $c);
		unlink('/tmp/container.php');
	}

	protected function createCompilerConfig($entryPoint)
	{
		return new CompilerConfig(
			'/tmp',
			new StubContainerConfig([$entryPoint], [])
		);
	}
}