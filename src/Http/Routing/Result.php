<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Result
 */

namespace Magnum\Http\Routing;

/**
 * Value object representing the results of the routing process
 */
class Result
{
	/**
	 * @var string The path used for matching this Routing Result
	 */
	protected $path;

	/**
	 * @var null|string[]
	 */
	protected $allowedMethods = [];

	/**
	 * @var array
	 */
	protected $params = [];

	/**
	 * @var string|bool The name of the route
	 */
	protected $routeName = false;

	/**
	 * @var Route The matched route
	 */
	protected $route;

	/**
	 * @var bool Whether or not the routing was successful
	 */
	protected $success = false;

	/**
	 * Create an instance representing a successful match
	 *
	 * @param array $params Parameters associated with the matched route
	 * @return Result
	 */
	public static function fromRoute(Route $route, array $params = []): self
	{
		$result          = new self();
		$result->success = true;
		$result->route   = $route;
		$result->params  = $params;

		return $result;
	}

	/**
	 * Create an instance representing a route failure.
	 *
	 * @param null|array $methods HTTP methods allowed for the current URI, if any.
	 *                            null is equivalent to allowing any HTTP method; empty array means none.
	 * @return Result
	 */
	public static function fromRouteFailure($path, ?array $methods): self
	{
		$result                 = new self();
		$result->path           = $path;
		$result->success        = false;
		$result->allowedMethods = $methods;

		return $result;
	}

	/**
	 * Returns if the route was matched successfully
	 */
	public function isSuccess(): bool
	{
		return $this->success;
	}

	/**
	 * Returns if the route match failed
	 */
	public function isFailure(): bool
	{
		return (!$this->success);
	}

	/**
	 * Returns if the route match failed due to a bad method
	 */
	public function isMethodFailure(): bool
	{
		return !($this->success || !$this->allowedMethods);
	}

	/**
	 * Retrieve the route that resulted in the route match.
	 *
	 * @return Route|false Route instance on success, false on failure.
	 */
	public function route()
	{
		return $this->isFailure() ? false : $this->route;
	}

	/**
	 * Return the path that was used for this result
	 *
	 * @return string
	 */
	public function path(): string
	{
		if (!$this->path && $this->route) {
			$this->path = $this->route->path();
		}

		return $this->path;
	}

	/**
	 * Return the route name, if not failure
	 *
	 * @return false|string False on failure, or the route name on success
	 */
	public function name()
	{
		if (!$this->routeName && $this->route) {
			$this->routeName = $this->route->name();
		}

		return $this->routeName;
	}

	/**
	 * Returns the matched params.
	 */
	public function params(): array
	{
		return $this->params;
	}

	/**
	 * Retrieve the allowed methods for the route failure.
	 *
	 * @return null|string[] HTTP methods allowed
	 */
	public function allowedMethods(): ?array
	{
		return $this->allowedMethods;
	}

	/**
	 * Only allow instantiation via factory methods.
	 */
	private function __construct()
	{
	}
}
