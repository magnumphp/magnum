<?php

namespace Magnum\Http\Middleware;

use Magnum\Http\Routing\Route;
use Middlewares\Utils\Factory;
use Pipeware\Stack;

class ActionHandlerTest
	extends TestCase
{
	public function testEmptyRouteReturns404()
	{
		$mw = new ActionHandler(
			$this->createMock(PipewareFactory::class),
			Factory::getResponseFactory()
		);

		$request = $this->buildRequest('GET', '/');
		$request->expects($this->once())->method('getAttribute')->with(Routing::ATTRIBUTE)->willReturn(null);

		self::assertStatusCode(404, $mw->process($request));
	}

	public function testRouteIsCalled()
	{
		$pf = $this->createMock(PipewareFactory::class);
		$mw = new ActionHandler(
			$pf,
			Factory::getResponseFactory()
		);

		$request = $this->buildRequest('GET', '/');
		$request->expects($this->once())
				->method('getAttribute')
				->with(Routing::ATTRIBUTE)
				->willReturn(new Route('', '', '', []));

		$stack = $this->createMock(Stack::class);
		$stack->method('handle')->willReturn(Factory::createResponse(333));
		$pf->expects($this->once())
		   ->method('newContainerInstance')
		   ->willReturn($stack);

		self::assertStatusCode(333, $mw->process($request));
	}
}