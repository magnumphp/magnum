<?php

namespace Magnum\Http\Routing;

use Psr\Container\ContainerInterface;

class Factory
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var array The cached data
	 */
	protected $cache = [];

	/**
	 * @var bool Whether or not the cache has changed
	 */
	protected $changed = false;

	public function __construct($container, $cacheFile = null)
	{
		$this->container = $container;
		if (file_exists($cacheFile)) {
			$this->cache = json_decode(file_get_contents($cacheFile), true);
		}
	}

	public function __destruct()
	{
		if (isset($this->cacheFile) && $this->changed) {
			file_put_contents($this->cacheFile, json_encode($this->cache));
		}
	}

	public function create($methods, $pattern, $callable, $groups, $counter)
	{
		$route = new Route($methods, $pattern, $callable, $groups, $counter);

		is_string($callable) && $this->loadRouteMiddleware($route, $callable);

		return $route;
	}

	protected function loadRouteMiddleware(Route $route, $callable)
	{
		if (class_exists($callable) && method_exists($callable, 'middleware')) {
			$this->cache[$callable] = call_user_func([$callable, 'middleware']);
			$this->changed          = true;
		}
		elseif ($this->container->has($key = "middleware/{$callable}")) {
			$this->cache[$callable] = $this->container->get($key);
			$this->changed          = true;
		}

		if (isset($this->cache[$callable])) {
			foreach ($this->cache[$callable] as $middleware) {
				$route->add($middleware);
			}
		}
	}
}