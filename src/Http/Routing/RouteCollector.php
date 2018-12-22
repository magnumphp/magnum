<?php

namespace Magnum\Http\Routing;

use FastRoute\RouteCollector as FastRouteCollector;

class RouteCollector
{
	/**
	 * @var FastRouteCollector
	 */
	protected $routeCollector;

	/**
	 * @var array List of the named routes
	 */
	protected $namedRoutes;

	public function __construct(FastRouteCollector $routeCollector)
	{
		$this->routeCollector = $routeCollector;
	}

	public function get(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['GET'], $path, $name, $middleware);
	}

	public function post(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['POST'], $path, $name, $middleware);
	}

	public function put(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['PUT'], $path, $name, $middleware);
	}

	public function patch(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['PATCH'], $path, $name, $middleware);
	}

	public function delete(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['DELETE'], $path, $name, $middleware);
	}

	public function options(string $path, string $name, ...$middleware)
	{
		$this->addRoute(['OPTIONS'], $path, $name, $middleware);
	}

	public function any(string $path, string $name, ...$middleware)
	{
		$this->addRoute([Route::METHOD_ANY], $path, $name, $middleware);
	}

	/**
	 * Returns the data required by the FastRoute\Dispatcher's
	 */
	public function dispatchData(): array
	{
		return $this->routeCollector->getData();
	}

	/**
	 * Returns the list of named routes
	 */
	public function namedRoutes()
	{
		return $this->namedRoutes;
	}

	/**
	 * Adds the route to the RouteCollector and stores the name association
	 *
	 * @param array  $methods     List of methods for the route
	 * @param string $path        The path of the route
	 * @param string $name        The name of the route
	 * @param array  $middleware  The middleware used for the route
	 */
	protected function addRoute($methods, $path, $name, $middleware)
	{
		$this->routeCollector->addRoute($methods, $path, [$name, $middleware]);
		$this->namedRoutes[$name] = $path;
	}
}