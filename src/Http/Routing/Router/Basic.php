<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Router\Basic
 */

namespace Magnum\Http\Routing\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Magnum\Http\Routing\Result;
use Magnum\Http\Routing\Route;
use Magnum\Http\Routing\Router;
use Psr\Http\Message\RequestInterface;

class Basic
	implements Router
{
	/**
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var RouteParser
	 */
	protected $routeParser;

	/**
	 * @var array List of named routes
	 */
	protected $namedRoutes = [];

	public function __construct(Dispatcher $dispatcher, RouteParser $routeParser, $namedRoutes)
	{
		$this->dispatcher  = $dispatcher;
		$this->routeParser = $routeParser;
		$this->namedRoutes = $namedRoutes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(RequestInterface $request): Result
	{
		$path   = '/' . trim(rawurldecode($request->getUri()->getPath()), '/');
		$result = $this->dispatcher->dispatch(
			$request->getMethod(),
			$path
		);

		if ($result[0] === Dispatcher::FOUND) {
			list($status, list($name, $middleware), $params) = $result;

			return Result::fromRoute(
				new Route($request->getMethod(), $path, $name, $middleware),
				$params
			);
		}

		return Result::fromRouteFailure($path, $result[1] ?? []);
	}

	/**
	 * {@inheritdoc}
	 */
	public function generateUri(string $name, ?array $params = [], ?array $query = []): string
	{
		if (empty($this->namedRoutes[$name])) {
			throw new \RuntimeException("Route not found: {$name}");
		}

		$routes = array_reverse($this->routeParser->parse($this->namedRoutes[$name]));
		$path   = [];
		$params = $params ?? [];
		$query  = $query ?? [];

		foreach ($routes as $route) {
			foreach ($route as $segment) {
				if (is_string($segment) || isset($params[$segment[0]])) {
					continue;
				}

				// we are missing parameters for this route continue on
				continue 2;
			}

			foreach ($route as $segment) {
				$path[] = is_string($segment)
					? $segment
					: $params[$segment[0]];
			}

			return implode('', $path) . (empty($query) ? '' : ('?' . http_build_query($query)));
		}

		throw new \InvalidArgumentException("Route `{$name}` is missing parameters.");
	}
}