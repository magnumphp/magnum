<?php

namespace Magnum\Console;

use Magnum\Console\Exception\InvalidCommandHandler;
use Magnum\Console\Fixture\TestHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

class CommandConfigTest
	extends TestCase
{
	public function testHandlerCanBeSetAndRetrieved()
	{
		$cc = new CommandConfig('test', null);
		$cc->setHandler('test-command');
		self::assertEquals('test-command', $cc->getHandler());
	}

	public function testEndReturnsApp()
	{
		$app = new Application(new Container());
		$cc  = new CommandConfig('test', $app);
		self::assertSame($app, $cc->end());
	}

	public function testExecuteDelegatesToCommandHandler()
	{
		$cc = new CommandConfig('test', new Application(new Container()));
		$cc->setHandler(new TestHandler());

		$c = new CommandTester($cc);
		$c->execute([]);

		self::assertTrue($cc->getHandler()->ran);
	}

	public function testExecuteRunsCallable()
	{
		$ran = false;
		$cc  = new CommandConfig('test', new Application(new Container()));
		$cc->setHandler(
			function () use (&$ran) {
				$ran = true;
			}
		);

		$c = new CommandTester($cc);
		$c->execute([]);

		self::assertTrue($ran);
	}

	public function testExecuteThrowsException()
	{
		$cc = new CommandConfig('test', new Application(new Container()));
		$cc->setHandler(
			new class
			{
			}
		);

		$c = new CommandTester($cc);

		$this->expectException(InvalidCommandHandler::class);

		$c->execute([]);
	}
}