<?php

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

	public static function setInstance($instance)
	{
		self::$instance = $instance;
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
		return call_user_func_array(array(static::$instance, $method), $args);
	}
}