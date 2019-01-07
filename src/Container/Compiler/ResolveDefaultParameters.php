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