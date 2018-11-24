<?php
declare(strict_types=1);

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Config\ContextDependentParamHint;
use Magnum\Container\Config\ParamHint;
use Magnum\Container\Exception\MissingClass;
use Magnum\Container\Param\StringParam;
use Magnum\Container\Fixture\ConstructorA;
use Magnum\Container\Fixture\ConstructorB;
use Magnum\Container\Fixture\ConstructorC;
use Magnum\Container\Fixture\ConstructorD;
use Magnum\Container\Fixture\ConstructorE;
use Magnum\Container\Fixture\NonOptionalConstructor;
use Magnum\Container\Stub\StubContainerConfig;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WoohooLabs\Zen\Config\Hint\ContextDependentDefinitionHint;
use WoohooLabs\Zen\Container\Definition\ClassDefinition;
use WoohooLabs\Zen\Container\Definition\ReferenceDefinition;
use WoohooLabs\Zen\Container\Definition\SelfDefinition;
use WoohooLabs\Zen\Exception\ContainerException;

/**
 * @copyright 2018, Woohoo Labs
 * @license   MIT
 */
class DependencyResolverTest
	extends TestCase
{
	public function testResolveDefinedConstructorDependencies()
	{
		$ep                 = new ClassEntryPoint(ConstructorA::class, ['c' => ConstructorD::class, 'e' => 'test']);
		$dependencyResolver = $this->createDependencyResolver($ep);
		$dependencyResolver->resolveEntryPoints();

		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				ConstructorA::class           => ClassDefinition::singleton(ConstructorA::class)
																->addRequiredConstructorArgument(ConstructorB::class)
																->addRequiredConstructorArgument(ConstructorD::class)
																->addOptionalConstructorArgument(true)
																->addOptionalConstructorArgument('test')
																->resolveDependencies(),
				ConstructorB::class           => ClassDefinition::singleton(ConstructorB::class)
																->resolveDependencies(),
				ConstructorD::class           => ClassDefinition::singleton(ConstructorD::class)
																->resolveDependencies(),
			],
			$dependencyResolver->getDefinitions()
		);
	}

	public function testUsesAnnotations()
	{
		$ep             = new ClassEntryPoint(ConstructorA::class, ['e' => 'test']);
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], []),
			]
		);
		$compilerConfig->enableAnnotations();
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$dependencyResolver->resolveEntryPoints();

		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				ConstructorA::class           => ClassDefinition::singleton(ConstructorA::class)
																->addRequiredConstructorArgument(ConstructorB::class)
																->addRequiredConstructorArgument(ConstructorC::class)
																->addOptionalConstructorArgument(true)
																->addOptionalConstructorArgument('test')
																->resolveDependencies(),
				ConstructorB::class           => ClassDefinition::singleton(ConstructorB::class)
																->resolveDependencies(),
				ConstructorC::class           => ClassDefinition::singleton(ConstructorC::class)
																->addRequiredConstructorArgument(ConstructorD::class)
																->resolveDependencies(),
				ConstructorD::class           => ClassDefinition::singleton(ConstructorD::class)
																->resolveDependencies(),
			],
			$dependencyResolver->getDefinitions()
		);
	}

	public function provideNonOptionConstructorParam()
	{
		return [
			['test', 'test'],
			[new StringParam('test1'), 'test1']
		];
	}
	/**
	 * @dataProvider provideNonOptionConstructorParam
	 */
	public function testNonOptionConstructorParam($payload, $expected)
	{
		$ep             = new ClassEntryPoint(NonOptionalConstructor::class, ['param' => $payload]);
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], []),
			]
		);
		$compilerConfig->enableAnnotations();
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$dependencyResolver->resolveEntryPoints();

		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				NonOptionalConstructor::class => ClassDefinition::singleton(NonOptionalConstructor::class)
																->addOptionalConstructorArgument($payload)
																->addOptionalConstructorArgument(null)
																->resolveDependencies()
			],
			$dependencyResolver->getDefinitions()
		);
	}

	public function xtestCustomParam()
	{
		$ep             = new ClassEntryPoint(NonOptionalConstructor::class, ['param' => new StringParam('test')]);
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], []),
			]
		);
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$dependencyResolver->resolveEntryPoints();
		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				NonOptionalConstructor::class => ClassDefinition::singleton(NonOptionalConstructor::class)
																->addOptionalConstructorArgument(new StringParam("test"))
																->addOptionalConstructorArgument(null)
																->resolveDependencies()
			],
			$dependencyResolver->getDefinitions()
		);
	}

	public function testParamHint()
	{
		$ep             = new ClassEntryPoint(NonOptionalConstructor::class);
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], [
					'param' => new StringParam('test'),
					NonOptionalConstructor::class . '.altParam' => new StringParam('alt')
				]),
			]
		);
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$dependencyResolver->resolveEntryPoints();
		$definitions = $dependencyResolver->getDefinitions();
		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				NonOptionalConstructor::class => ClassDefinition::singleton(NonOptionalConstructor::class)
																->addOptionalConstructorArgument(new StringParam("test"))
																->addOptionalConstructorArgument(new StringParam('alt'))
																->resolveDependencies()
			],
			$definitions
		);
	}

	public function testResolveConstructorThrowsExceptionForNonReflectedClass()
	{
		$ep             = new ClassEntryPoint('My\NonExistingClass');
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], []),
			]
		);
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$this->expectException(MissingClass::class);
		$dependencyResolver->resolveEntryPoints();
	}

	public function testDependenciesAreResolvedForNonClassDefinitions()
	{
		$this->markTestSkipped("@TODO");
	}

	public function testHintsResolveContextDependentDefinition()
	{
		//'param' => new StringParam('test');
		$ep             = new ClassEntryPoint(NonOptionalConstructor::class);
		$cdd = new ContextDependentParamHint(new StringParam('test'));
		;
		$compilerConfig = new CompilerConfig(
			'/tmp',
			[
				new StubContainerConfig([$ep], ['param' => $cdd]),
			]
		);
		$dependencyResolver = new DependencyResolver($compilerConfig);
		$dependencyResolver->resolveEntryPoints();
		$this->assertEquals(
			[
				"Magnum\\Compiled\\Container" => new SelfDefinition("Magnum\\Compiled\\Container"),
				ContainerInterface::class     => new ReferenceDefinition(ContainerInterface::class, "Magnum\\Compiled\\Container"),
				NonOptionalConstructor::class => ClassDefinition::singleton(NonOptionalConstructor::class)
																->addOptionalConstructorArgument(new StringParam("test"))
																->addOptionalConstructorArgument(null)
																->resolveDependencies()
			],
			$dependencyResolver->getDefinitions()
		);
	}

	private function createDependencyResolver($entryPoint, array $definitionHints = []): DependencyResolver
	{
		return new DependencyResolver(
			new CompilerConfig(
				'/tmp',
				[
					new StubContainerConfig([$entryPoint], $definitionHints),
				]
			)
		);
	}
}