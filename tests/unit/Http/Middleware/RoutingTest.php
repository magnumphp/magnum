<?php

namespace Magnum\Http\Middleware;

use FastRoute\RouteParser;
use Magnum\Container\Builder;
use Magnum\Http\ServiceProvider;
use Magnum\Http\Stub\Routes;
use Magnum\Http\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Turbo\Provider\RouteProvider;

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
			new Routing($this->buildMiddleware($routes), $notFoundHandler, $badMethodHandler),
			$this->buildRequest($method, $path)
		];
	}

	protected function container($notFoundHandler = null, $badMethodHandler = null)
	{
		$builder = new Builder();
		(new ServiceProvider())->register($builder);
		$routing = $builder->register(Routing::class);

		$routing->setPublic(true);
		$notFoundHandler && $routing->setArgument('$notFoundHandler', $notFoundHandler);
		$badMethodHandler && $routing->setArgument('$badMethodHandler', $badMethodHandler);

		$builder->register(RouteProvider::class, Routes::class);

		return $builder->container();
	}

	public function testFoundRoute()
	{
		$mw      = $this->container()->get(Routing::class);
		$handler = $this->createMock(RequestHandlerInterface::class);
		$request = $this->buildRequest('GET', '/');

		$handler->method('handle')
				->willReturn($this->createMock(ResponseInterface::class));
		$mw->process($request, $handler);

		$attrs = $request->getAttributes();
		self::assertArrayHasKey(Routing::ATTRIBUTE, $attrs);
		self::assertArrayHasKey(Routing::RESULT_ATTRIBUTE, $attrs);
	}

	public function testFailureReturns404ResponseWithoutHandler()
	{
		$mw      = $this->container()->get(Routing::class);
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
		$mw      = $this->container()->get(Routing::class);
		$request = $this->buildRequest('POST', '/');
		$handler = $this->createMock(RequestHandlerInterface::class);

		$handler->expects($this->never())->method('handle');

		/** @var ResponseInterface $r */
		$r      = $mw->process($request, $handler);
		$result = $request->getAttribute(Routing::RESULT_ATTRIBUTE);

		self::assertInstanceOf(ResponseInterface::class, $r);
		self::assertEquals(405, $r->getStatusCode());
	}

	public function testProcessHonorsNotFoundHandler()
	{
		$custom = $this->createMock(RequestHandlerInterface::class);
		$custom->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		$mw      = $this->container($custom)->get(Routing::class);
		$request = $this->buildRequest('GET', '/test/no');

		$mw->process($request, $handler);
	}

	public function testProcessHonorsBadMethodHandler()
	{
		$custom = $this->createMock(RequestHandlerInterface::class);
		$custom->expects($this->once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$handler = $this->createMock(RequestHandlerInterface::class);
		$handler->expects($this->never())->method('handle');

		$mw      = $this->container(null, $custom)->get(Routing::class);
		$request = $this->buildRequest('POST', '/');

		$mw->process($request, $handler);
	}
}