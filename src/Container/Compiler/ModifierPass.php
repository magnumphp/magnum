<?php

/**
 * @file
 * Contains Magnum\Container\Compiler\ModifierPass
 */

namespace Magnum\Container\Compiler;

use Magnum\Container\Modifier;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Allows to modify a definition before it's removed and any further optimizations are done
 *
 * @package Magnum\Container\Compiler
 */
class ModifierPass
	implements CompilerPassInterface
{
	/**
	 * @var array<Modifier> List of modifiers
	 */
	protected $modifiers = [];

	/**
	 * @param string $id The definition ID to modifier
	 *
	 * @return Modifier
	 */
	public function get(string $id): Modifier
	{
		return $this->modifiers[$id] ?? ($this->modifiers[$id] = new Modifier($id));
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		foreach ($this->modifiers as $class => $modifier) {
			$alias = null;
			if ($container->hasAlias($class)) {
				$alias = $class;
				$class = (string)$container->getAlias($class);
			}

			if ($container->hasDefinition($class)) {
				$modifier->apply($container->getDefinition($class), $alias);
			}
		}
	}
}