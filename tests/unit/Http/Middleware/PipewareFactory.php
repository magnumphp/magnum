<?php

namespace Magnum\Http\Middleware;

use Magnum\Fixture\Middleware\Test;
use Middlewares\Utils\Factory;
use Pipeware\Pipeline\Basic;
use Pipeware\Pipeline\Containerized;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class PipewareFactoryTest
	extends TestCase
{
	public function testNewBasicInstance()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturnOnConsecutiveCalls([true, false]);
		$c->expects($this->once())
		  ->method('get')
		  ->with(Basic::class)
		  ->willReturn(new Basic);

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newBasicInstance());
	}

	public function testNewBasicInstanceCreatesWithoutContainer()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturn(false);

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newBasicInstance());
	}

	public function testNewContainerInstance()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturnOnConsecutiveCalls([true, false]);
		$c->expects($this->once())
		  ->method('get')
		  ->with(Containerized::class)
		  ->willReturn(new Containerized($c));

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newContainerInstance());
	}

	public function testNewContainerInstanceCreatesWithoutContainer()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturn(false);

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newContainerInstance());
	}

	public function testNewStack()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturnOnConsecutiveCalls(false);

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newStack(new Basic));
	}

	public function testNewStackRetrievesProcessorFromContainer()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturnOnConsecutiveCalls(true);
		$c->expects($this->once())
		  ->method('get')
		  ->with(Processor::class)
		  ->willReturn(new Processor(Factory::getResponseFactory()));

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		self::assertInstanceOf(Stack::class, $mw->newStack(new Basic));
	}

	public function testMiddlewareIsPipelined()
	{
		$c = $this->createMock(ContainerInterface::class);
		$c->method('has')->willReturnOnConsecutiveCalls(true);
		$c->method('get')
		  ->willReturnOnConsecutiveCalls(
			  new Basic(),
			  new Processor(Factory::getResponseFactory())
		  );

		$mw = new PipewareFactory($c, Factory::getResponseFactory());

		$s = $mw->newContainerInstance(
			[
				$t1 = new Test(),
				$t2 = new Test()
			]
		);

		$s->handle($this->createMock(ServerRequestInterface::class));

		self::assertEquals('handler', $t1->did);
		self::assertEquals('handler', $t2->did);
	}
}