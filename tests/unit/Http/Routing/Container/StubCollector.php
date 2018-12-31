<?php

namespace Magnum\Http\Routing\Container;

use Magnum\Http\Routing\RouteCollector;

class StubCollector
	extends RouteCollector
{
	public $routes = [];

	public function __construct()
	{
	}

	public function namedRoutes()
	{
		return $this->routes;
	}

	public function dispatchData(): array
	{
		return $this->routes;
	}

	protected function addRoute($methods, $path, $name, $middleware)
	{
		foreach ($methods as $method) {
			$this->routes[$method][$path] = [$name, $middleware];
		}
	}
}