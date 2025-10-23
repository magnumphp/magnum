<?php

namespace Magnum\Console\Container;

use Magnum\Console\CommandLoader\Commands;
use Magnum\Console\Fixture\TestCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConsoleCommandPassTest
	extends TestCase
{
	protected $container;
	public function setUp(): void
	{
		$this->container = new ContainerBuilder();
		$this->container->addCompilerPass(new ConsoleCommandPass());
		$this->container->register(Commands::class)->setPublic(true);

		$this->container->setParameter('path', '/test');
		$this->container->register('test', TestCommand::class)->addTag(ConsoleCommandPass::TAG_NAME)->setPublic(true);
		$this->container->compile();
	}

	public function testConsoleCommandsAreSet()
	{
		self::assertEquals(['test'], $this->container->getParameter('console.commands'));
	}

	public function testCommandsClassCommandsArgumentIsSet()
	{
		// we should have 2 arguments
		self::assertEquals(
			[
				'/test/data/bootstrap',
				'%console.commands%'
			],
			$this->container->getDefinition(Commands::class)->getArguments()
		);
	}
}