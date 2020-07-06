<?php

/**
 * @file
 * Contains Magnum\ProxyManager\StaticProxy
 */

namespace Magnum\ProxyManager;

use ReStatic\StaticProxy as ReStaticProxy;

/**
 * Overrides the ReStatic\StaticProxy to cache the instance instead of hitting the container on each call
 */
abstract class StaticProxy
	extends ReStaticProxy
{
	protected static $instance;

	/**
	 * {@inheritDoc}
	 */
	public static function getInstance()
	{
		return static::$instance ?? (static::$instance = parent::getInstance());
	}
}
