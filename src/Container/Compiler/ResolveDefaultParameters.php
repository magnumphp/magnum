<?php

/**
 * @file
 * Contains Magnum\Container\Compiler\ResolveDefaultParameters
 */

namespace Magnum\Container\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements default parameters in the container.
 *
 * If the parameter is not defined, specify the default for it
 *
 * @package Magnum\Container\Compiler
 */
class ResolveDefaultParameters
	implements CompilerPassInterface
{
	protected $params = [];

	/**
	 * Sets the parameter value
	 *
	 * @param $id
	 * @param $value
	 */
	public function param($id, $value)
	{
		$this->params[$id] = $value;
	}

	/**
	 * Return the parameter value
	 *
	 * @param string     $id
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public function get($id, $default = null)
	{
		return $this->params[$id] ?? $default;
	}

	/**
	 * @param string $id The name of the service
	 * @return bool True if the service exists, False otherwise
	 */
	public function has($id): bool
	{
		return array_key_exists($id, $this->params);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(ContainerBuilder $container)
	{
		foreach ($this->params as $param => $value) {
			if (!$container->hasParameter($param)) {
				$container->setParameter($param, $value);
			}
		}
	}
}