<?php

/**
 * @file
 * Contains Magnum\ProxyManager\StaticProxy
 */

namespace Magnum\ProxyManager;

use Magnum\Container\Builder;
use Psr\Container\ContainerInterface;
use ReStatic\StaticProxy as ReStaticProxy;

/**
 * Overrides the ReStatic\StaticProxy to cache the instance instead of hitting the container on each call
 */
abstract class StaticProxy
	extends ReStaticProxy
{
	protected static $instance;

	/**
	 * Sets the Container that will be used to retrieve the Proxy Subject
	 *
	 * @param ContainerInterface $container The Container that provides the real Proxy Subject
	 *
	 * @return mixed
	 */
	public static function setContainer($container)
	{
		parent::setContainer($container instanceof Builder ? $container->container() : $container);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getInstance()
	{
		return static::$instance ?? (static::$instance = parent::getInstance());
	}
}
