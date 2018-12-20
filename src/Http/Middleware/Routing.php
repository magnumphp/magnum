<?php

namespace Magnum\Http\Middleware;

use Magnum\Http\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Routing
	implements MiddlewareInterface
{
	/**
	 * @var Router
	 */
	protected $router;

	/**
	 * @var RequestHandlerInterface
	 */
	protected $notFoundHandler;

	/**
	 * @var RequestHandlerInterface
	 */
	protected $badMethodHandler;

	public function __construct(
		Router $router,
		RequestHandlerInterface $notFoundHandler,
		RequestHandlerInterface $badMethodHandler
	) {
		$this->router           = $router;
		$this->notFoundHandler  = $notFoundHandler;
		$this->badMethodHandler = $badMethodHandler;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$result  = $this->router->match($request);
		$request = $request->withAttribute('routing_result', $result);
		if ($result->isSuccess()) {
			$request = $request->withAttribute('route', $result->route());
		}
		elseif ($result->isFailure()) {
			$handler = $this->notFoundHandler;
		}
		elseif ($result->isMethodFailure()) {
			$handler = $this->badMethodHandler;
		}

		return $handler->handle($request);
	}
}