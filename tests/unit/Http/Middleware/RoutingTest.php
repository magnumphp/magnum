<?php

namespace Magnum\Http\Middleware;

use FastRoute\RouteParser;
use Magnum\Http\Routing\Result;
use Magnum\Http\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingTest
	extends TestCase
{
	/**
	 * @var RouteParser\Std;
	 */
	protected $routeParser;

	public function build($method, $path, $routes, $notFoundHandler = null, $badMethodHandler = null)
	{
		return [
			new Routing($this->buildRouter($routes), $notFoundHandler, $badMethodHandler),
			$this->buildRequest($method, $path)
		];
	}

	public function testProcessGood()
	{
		$mw      = new Routing($this->buildRouter(['get' => ['/', 'test', 'get']]));
		$request = $this->buildRequest('GET', '/');
		$handler = $this->createMock(RequestHandlerInterface::class);

		$handler->method('handle')->willReturn($this->createMock(ResponseInterface::class));
		$mw->process($request, $handler);

		self::assertArrayHasKey('routing_result', $request->attrs);
		self::assertInstanceOf(Result::class, $request->attrs['routing_result']);

		self::assertArrayHasKey('route', $request->attrs);
		self::assertInstanceOf(Route::class, $request->attrs['route']);
	}

	public function testFailureReturns404ResponseWithoutHandler()
	{
		$mw      = new Routing($this->buildRouter(['get' => ['/', 'test', 'get']]));
		$request = $this->buildRequest('GET', '/test');
		$handler = $this->createMock(RequestHandlerInterface::class);

		$handler->expects($this->never())->method('handle');

		/** @var ResponseInterface $r */
		$r = $mw->process($request, $handler);
		self::assertInstanceOf(ResponseInterface::class, $r);
		self::assertEquals(404, $r->getStatusCode());
	}

	public function testFailureReturns405ResponseWithoutHandler()
	{
		$mw      = new Routing($this->buildRouter(['get' => ['/', 'test', 'get']]));
		$request = $this->buildRequest('POST', '/');
		$handler = $this->createMock(RequestHandlerInterface::class);

		$handler->expects($this->never())->method('handle');

		/** @var ResponseInterface $r */
		$r      = $mw->process($request, $handler);
		$result = $request->attrs['routing_result'];

		self::assertInstanceOf(ResponseInterface::class, $r);
		self::assertEquals(405, $r->getStatusCode());
	}

	public function testProcessHonorsNotFoundHandler()
	{
		$custom  = $this->createMock(RequestHandlerInterface::class);
		$custom->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		$mw      = new Routing($this->buildRouter(['get' => ['/', 'test', 'get']]), $custom);
		$request = $this->buildRequest('GET', '/test/no');

		$mw->process($request, $handler);
	}

	public function testProcessHonorsBadMethodHandler()
	{
		$custom  = $this->createMock(RequestHandlerInterface::class);
		$custom->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		$mw      = new Routing($this->buildRouter(['get' => ['/', 'test', 'get']]), null, $custom);
		$request = $this->buildRequest('POST', '/');

		$mw->process($request, $handler);
	}
}