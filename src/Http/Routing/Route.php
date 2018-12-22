<?php

namespace Magnum\Http\Routing;

/**
 * Value object representing a Route
 *
 * @package Magnum\Http\Routing
 */
class Route
{
	const METHOD_ANY = '*';

	/**
	 * @var string The method used
	 */
	protected $method;

	/**
	 * @var string The path used for the route
	 */
	protected $path;

	/**
	 * @var string The name of the route
	 */
	protected $name;

	/**
	 * @var array|string The middleware for the route
	 */
	protected $middleware;

	public function __construct($method, string $path, string $name, array $middleware)
	{
		$this->method     = $method;
		$this->path       = $path;
		$this->name       = $name;
		$this->middleware = $middleware;
	}

	/**
	 * Returns the name of this route
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns the path for this route
	 */
	public function path(): string
	{
		return $this->path;
	}

	/**
	 * Returns the middleware for this route
	 */
	public function middleware(): array
	{
		return $this->middleware;
	}

	/**
	 * Returns the method used on this route
	 */
	public function method(): string
	{
		return $this->method;
	}
}