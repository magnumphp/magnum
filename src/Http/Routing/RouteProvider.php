<?php

/**
 * @file
 * Contains Magnum\Http\Routing\RouteProvider
 */

namespace Magnum\Http\Routing;

/**
 * A RouteProvider registers it's routes with the RouteCollector
 *
 * @package Magnum\Http\Routing
 */
interface RouteProvider
{
	/**
	 * Register the routes
	 * @param RouteCollector $collector
	 * @return mixed
	 */
	public function routes(RouteCollector $collector);
}