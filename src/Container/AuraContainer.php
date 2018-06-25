<?php

namespace Magnum\Container;

use Aura\Di\Container;
use Aura\Di\Exception\ServiceNotFound;
use Psr\Container\ContainerInterface;

/**
 * Wraps the Aura DI container to treat the values/params as part of get/has
 *
 * @package Magnum\Container
 */
class AuraContainer
	implements ContainerInterface
{
	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function get($id)
	{
		if ($this->container->has($id)) {
			return $this->container->get($id);
		}

		if (isset($this->container->values[$id])) {
			return $this->container->values[$id];
		}

		throw new ServiceNotFound("$id not found");
	}

	public function has($id)
	{
		if ($this->container->has($id)) {
			return true;
		}

		return isset($this->container->values[$id]);
	}

	/**
	 * Catchall proxy
	 *
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array([$this->container, $name], $arguments);
	}
}