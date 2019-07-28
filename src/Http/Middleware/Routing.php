<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\ActionHandler
 */

namespace Magnum\Http\Middleware;

use Magnum\Http\Request\Handler\StaticResponse;
use Magnum\Http\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Routing middleware
 *
 * @package Magnum\Http\Middleware
 */
class Routing
	implements MiddlewareInterface
{
	use IsMiddleware;

	const ATTRIBUTE        = 'route';
	const RESULT_ATTRIBUTE = 'routing_result';

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
		?RequestHandlerInterface $notFoundHandler = null,
		?RequestHandlerInterface $badMethodHandler = null
	) {
		$this->router           = $router;
		$this->notFoundHandler  = $notFoundHandler;
		$this->badMethodHandler = $badMethodHandler;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$result  = $this->router->match($request);
		$request = $request->withAttribute(self::RESULT_ATTRIBUTE, $result);
		if ($result->isSuccess()) {
			$request = $request->withAttribute(self::ATTRIBUTE, $result->route());
		}
		elseif ($result->isMethodFailure()) {
			$handler = $this->badMethodHandler ?? $this->createHandler(405);
		}
		elseif ($result->isFailure()) {
			$handler = $this->notFoundHandler ?? $this->createHandler(404);
		}

		return $handler->handle($request);
	}

	/**
	 * Creates a RequestHandler capable of answering the handle method
	 *
	 * @param int $code
	 * @return RequestHandlerInterface
	 */
	protected function createHandler($code): RequestHandlerInterface
	{
		return new StaticResponse($this->createResponse($code));
	}
}