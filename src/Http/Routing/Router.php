<?php

namespace Magnum\Http\Routing;

use FastRoute\RouteParser;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Extends Slim's router to provide the method verbs since it's not part of the app
 *
 * @package Magnum\Http
 */
class Router
	extends \Slim\Router
{
	protected $routeFactory;

	public function __construct(RouteParser $parser = null, $routeFactory = null)
	{
		$this->routeFactory = $routeFactory;
		parent::__construct($parser);
	}

	/********************************************************************************
	 * Router proxy methods
	 *******************************************************************************/

	/**
	 * Add GET route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function get($pattern, $callable)
	{
		return $this->map(['GET'], $pattern, $callable);
	}

	/**
	 * Add POST route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function post($pattern, $callable)
	{
		return $this->map(['POST'], $pattern, $callable);
	}

	/**
	 * Add PUT route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function put($pattern, $callable)
	{
		return $this->map(['PUT'], $pattern, $callable);
	}

	/**
	 * Add PATCH route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function patch($pattern, $callable)
	{
		return $this->map(['PATCH'], $pattern, $callable);
	}

	/**
	 * Add DELETE route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function delete($pattern, $callable)
	{
		return $this->map(['DELETE'], $pattern, $callable);
	}

	/**
	 * Add OPTIONS route
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function options($pattern, $callable)
	{
		return $this->map(['OPTIONS'], $pattern, $callable);
	}

	/**
	 * Add route for any HTTP method
	 *
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return \Slim\Interfaces\RouteInterface
	 */
	public function any($pattern, $callable)
	{
		return $this->map(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $callable);
	}

	protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
	{
		return $this->routeFactory->newRoute($methods, $pattern, $callable, $this->routeGroups, $this->routeCounter);
	}

	public function pushGroup(string $pattern, $callable = null): RouteGroupInterface
	{
		$inline = function () {
		};

		return parent::pushGroup($pattern, $callable ?: $inline);
	}
}