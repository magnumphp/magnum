<?php
declare(strict_types=1);

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Fixture\ConstructorA;
use Magnum\Container\Stub\StubContainerConfig;
use Magnum\Container\Stub\TestProvider;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class CompilerConfigTest
	extends TestCase
{

	public function testGetContainerConfigs()
	{
		$ep = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$c  = new StubContainerConfig([$ep], []);
		$cc = new CompilerConfig(
			'/tmp',
			$c
		);

		self::assertEquals(
			[
				$c
			],
			$cc->getContainerConfigs()
		);
	}

	public function testGetContainerConfigsWithConstructorArray()
	{
		$ep = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$c  = new StubContainerConfig([$ep], []);
		$cc = new CompilerConfig(
			'/tmp',
			[$c]
		);

		self::assertEquals(
			[
				$c
			],
			$cc->getContainerConfigs()
		);
	}

	public function testRegister()
	{
		$ep = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$c  = new StubContainerConfig([$ep], []);
		$cc = new CompilerConfig(
			'/tmp'
		);
		$cc->register($c);
		self::assertEquals(
			[
				$c
			],
			$cc->getContainerConfigs()
		);
	}

	public function testAnnotationsAreDisabledByDefault()
	{
		$cc = new CompilerConfig('/tmp');
		self::assertFalse($cc->useAnnotations());
	}

	public function testAnnotationsToggle()
	{
		$cc = new CompilerConfig('/tmp');
		$cc->enableAnnotations();
		self::assertTrue($cc->useAnnotations());
	}

	public function testCompilationIsEnabledByDefault()
	{
		$cc = new CompilerConfig('/tmp');
		self::assertTrue($cc->useCompilation());
	}

	public function testCompilationToggle()
	{
		$cc = new CompilerConfig('/tmp');
		$cc->disableCompilation();
		self::assertFalse($cc->useCompilation());
	}

	public function testIsCompiledReturnsFalse()
	{
		vfsStream::setup('home');
		$cc = new CompilerConfig("vfs://home");
		self::assertFalse($cc->isCompiled());
	}

	public function testIsCompiledReturnsTrue()
	{
		$cc = new CompilerConfig("vfs://home");

		$vfs = vfsStream::setup(
			'home',
			null,
			[
				'container.php' => '<?php test'
			]);

		self::assertTrue($cc->isCompiled());
	}

	public function testSaveCompiled()
	{
		$cc = new CompilerConfig("vfs://home");

		$vfs = vfsStream::setup(
			'home',
			null,
			[
				'container.php' => '<?php test'
			]);

		$cc->saveCompiled('testing');
		$s = file_get_contents("vfs://home/container.php");
		self::assertEquals('testing', $s);
	}

	public function testGetContainerConfigsCreatesProvider()
	{
		$cc = new CompilerConfig('/tmp', TestProvider::class);

		self::assertEquals(
			[
				new TestProvider()
			],
			$cc->getContainerConfigs()
		);
	}
}