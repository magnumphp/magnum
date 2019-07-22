<?php

namespace Magnum\Http\Middleware;

use FastRoute\DataGenerator\GroupCountBased as FastRouteGenerator;
use FastRoute\Dispatcher\GroupCountBased as FastRouteDispatcher;
use FastRoute\RouteParser;
use Magnum\Http\Routing\RouteCollector;
use Magnum\Http\Routing\Router\Basic;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestCase
	extends \PHPUnit\Framework\TestCase
{
	public static function assertStatusCode($code, $response)
	{
		self::assertEquals($code, $response->getStatusCode());
	}

	/**
	 * Returns a mock RequestHandler that should be called once
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildOnceRequestHandler(): RequestHandlerInterface
	{
		$response = $this->createMock(ResponseInterface::class);
		$response->method('getStatusCode')->willReturn(333);

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->once())->method('handle')->willReturn($response);

		return $handler;
	}

	/**
	 * Returns a mock RequestHandler that should never be called
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildNeverRequestHandler(): RequestHandlerInterface
	{
		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		return $handler;
	}

	/**
	 * Builds the router
	 *
	 * @param $routes
	 * @return Basic
	 */
	public function buildRouter($routes)
	{
		$routeParser    = new RouteParser\Std();
		$routeCollector = new RouteCollector(new \FastRoute\RouteCollector($routeParser, new FastRouteGenerator()));

		foreach ($routes as $method => $call) {
			$routeCollector->{$method}(...$call);
		}

		return new Basic(
			new FastRouteDispatcher($routeCollector->dispatchData()),
			$routeParser,
			$routeCollector->namedRoutes()
		);
	}

	/**
	 * Builds a request uri mock object
	 *
	 * @param $method
	 * @param $path
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	public function buildRequest($method, $path): ServerRequestInterface
	{
		$request        = $this->createMock(ServerRequestInterface::class);
		$uri            = $this->createMock(UriInterface::class);

		$uri->method('getPath')->willReturn($path);
		$request->attrs = [];
		$request->method('getUri')->willReturn($uri);
		$request->method('getMethod')->willReturn($method);

		$request->method('withAttribute')->willReturnCallback(
			function ($name, $value) use (&$request) {
				$request->attrs[$name] = $value;

				return $request;
			});

		return $request;
	}
}
