<?php

/**
 * @file
 * Contains Magnum\ProxyManager\Proxy
 */

namespace Magnum\ProxyManager;

/**
 * Proxy for the ProxyManager itself
 *
 * @package Magnum\ProxyManager
 */
class Proxy
{
	/**
	 * @var Manager
	 */
	protected static $instance;

	/**
	 * Sets the instance of the Manager
	 *
	 * @param Manager $instance
	 */
	public static function setInstance(Manager $instance)
	{
		static::$instance = $instance;
	}

	/**
	 * Performs the proxying of the statically called method to the Proxy Subject in the Container
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		return (static::$instance)->$method(...$args);
	}
}