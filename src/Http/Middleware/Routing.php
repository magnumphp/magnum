<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\Routing
 */

namespace Magnum\Http\Middleware;

use Magnum\Http\Message\StaticResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\RouteContext;
use Slim\Routing\RoutingResults;

/**
 * Routing middleware
 *
 * This uses the same attribute as Slim's RoutingMiddleware so that Slims RouteRunner functions properly.
 *
 * It functions differently from Slim in that it does not throw exceptions on invalid routing. Instead you would
 * set $notFoundHandler, or $badMethodHandler. This allows
 *
 * @package Magnum\Http\Middleware
 */
class Routing
	implements MiddlewareInterface
{
	use IsMiddleware;

	const ATTRIBUTE        = RouteContext::ROUTE;
	const RESULT_ATTRIBUTE = RouteContext::ROUTING_RESULTS;

	/**
	 * @var RouteParserInterface
	 */
	protected $routeParser;

	/**
	 * @var RouteResolverInterface
	 */
	protected $routeResolver;

	/**
	 * @var RequestHandlerInterface
	 */
	protected $notFoundHandler;

	/**
	 * @var RequestHandlerInterface
	 */
	protected $badMethodHandler;

	public function __construct(
		RouteResolverInterface $routeResolver,
		RouteParserInterface $routeParser,
		?RequestHandlerInterface $notFoundHandler = null,
		?RequestHandlerInterface $badMethodHandler = null
	) {
		$this->routeResolver    = $routeResolver;
		$this->routeParser      = $routeParser;
		$this->notFoundHandler  = $notFoundHandler;
		$this->badMethodHandler = $badMethodHandler;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$result = $this->routeResolver->computeRoutingResults(
			$request->getUri()->getPath(),
			$request->getMethod()
		);

		switch ($result->getRouteStatus()) {
			case RoutingResults::FOUND:
				$request = $request->withAttribute(
					self::ATTRIBUTE,
					$this->routeResolver
						->resolveRoute($result->getRouteIdentifier() ?? '')
						->prepare($result->getRouteArguments())
				);
				break;
			case RoutingResults::METHOD_NOT_ALLOWED:
				$handler = $this->badMethodHandler ?? $this->createHandler(405);
				break;
			case RoutingResults::NOT_FOUND:
				$handler = $this->notFoundHandler ?? $this->createHandler(404);
				break;
			default:
				$handler = $this->notFoundHandler ?? $this->createHandler(500);
		}

		return $handler->handle($request->withAttribute(self::RESULT_ATTRIBUTE, $result));
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