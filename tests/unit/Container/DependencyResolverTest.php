<?php
declare(strict_types=1);

namespace Magnum\Container;

use Magnum\Container\Config\ClassEntryPoint;
use Magnum\Container\Fixture\ConstructorA;
use Magnum\Container\Fixture\ConstructorB;
use Magnum\Container\Fixture\ConstructorC;
use Magnum\Container\Fixture\ConstructorD;
use Magnum\Container\Fixture\ConstructorE;
use Magnum\Container\Stub\StubContainerConfig;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WoohooLabs\Zen\Container\Definition\ClassDefinition;
use WoohooLabs\Zen\Container\Definition\ReferenceDefinition;
use WoohooLabs\Zen\Container\Definition\SelfDefinition;

/**
 * @copyright 2018, Woohoo Labs
 * @license MIT
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