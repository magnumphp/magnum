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

	protected $data;

	/**
	 * @var \Aura\Di\Resolver\Resolver
	 */
	protected $resolver;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->resolver  = $container->getInjectionFactory()->getResolver();
	}

	public function set($key, $value)
	{
		$this->loadValues();
		if (is_array($value) || is_string($value)) {
			$this->resolver->values[$key] = $value;
		}
		else {
			$this->container->set($key, $value);
		}

		return $this;
	}

	public function &__get($key)
	{
		return $this->resolver->__get($key);
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
		$this->loadValues();
		if ($this->container->has($id)) {
			return true;
		}

		return isset($this->data[$id]);
	}

	public function reload()
	{
		$this->loadValues(true);
	}

	protected function loadValues($reload = false)
	{
		if ($reload || !$this->data) {
			$this->data = $this->resolver->__get('values');
		}
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