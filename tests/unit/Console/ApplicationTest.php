<?php

namespace Magnum\Console;

use Magnum\Console\CommandLoader\Commands;
use Magnum\Console\Container\ConsoleCommandPass;
use Magnum\Console\Fixture\TestCommand;
use Magnum\Console\Fixture\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApplicationTest
	extends TestCase
{
	public function testBeginCommandReturnsCommandConfig()
	{
		$app = new Application(new Container());
		self::assertInstanceOf(CommandConfig::class, $app->beginCommand('test'));
	}

	public function testRunCommandUsesCommandHandler()
	{
		$app = new Application(new Container());

		$ran = false;
		$app->beginCommand('test')
			->setHandler(
				function () use (&$ran) {
					$ran = true;
				}
			);
		$app->setAutoExit(false);
		$app->run(
			new ArgvInput(['test', 'test']),
			new DummyOutput()
		);

		self::assertTrue($ran);
	}

	public function testRunLoadsCommandHandlerFromContainer()
	{
		$handler   = new TestHandler();
		$container = new Container();
		$app       = new Application($container);

		$container->set('test', $handler);
		$app->beginCommand('test')->setHandler('test');
		$app->setAutoExit(false);
		$app->run(
			new ArgvInput(['test', 'test']),
			new DummyOutput()
		);

		self::assertTrue($handler->ran);
	}

	public function testSetCommandLoader()
	{
		// set up a basic container
		$container = new ContainerBuilder();
		$container->addCompilerPass(new ConsoleCommandPass());
		$container->register(TestCommand::class)->addTag(ConsoleCommandPass::TAG_NAME);
		$container->register(Commands::class)->setPublic(true);
		$container->setParameter('path', '.');
		$container->register(Application::class)
				  ->setPublic(true)
				  ->setArgument('$container', new Reference(ContainerInterface::class))
				  ->setArgument('$commandLoader', new Reference(Commands::class));

		$o = new DummyOutput();
		$container->compile();

		$app = $container->get(Application::class);
		$app->setAutoExit(false);
		$app->run(
			new ArgvInput(['test', 'test-command']),
			$o
		);

		self::assertTrue(TestCommand::$seen);
	}
}