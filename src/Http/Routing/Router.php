<?php

/**
 * @file
 * Contains Magnum\Http\Routing\Router
 */

namespace Magnum\Http\Routing;

use Psr\Http\Message\RequestInterface;

/**
 * Contract for Routers
 *
 * @package Magnum\Http\Routing
 */
interface Router
{
	/**
	 *
	 * @param RequestInterface $request
	 * @return Result
	 */
	public function match(RequestInterface $request): Result;

	/**
	 * Generates a URI give the route
	 *
	 * @param string     $name   The name of the route
	 * @param array|null $params List of parameters
	 * @param array|null $query  List of query parameters to add on
	 * @return string
	 */
	public function generateUri(string $name, ?array $params = [], ?array $query = []): string;
}