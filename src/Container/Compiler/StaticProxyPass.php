<?php

/**
 * @file
 * Contains Magnum\Container\Compiler\StaticProxyPass
 */

namespace Magnum\Container\Compiler;

use Magnum\ProxyManager\Manager as ProxyManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Handles setting up the Magnum Proxy Manager
 *
 * @package Magnum\Container\Compiler
 */
class StaticProxyPass
	implements CompilerPassInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function process(ContainerBuilder $container)
	{
		if (method_exists($container, 'hasParameter') && $container->hasParameter('proxies')) {
			$definition = (new Definition(ProxyManager::class))
				->setFactory([ProxyManager::class, 'factory'])
				->setArgument('$proxies', '%proxies%')
				->setArgument('$aliasLoader', null)
				->setPublic(true)
			;

			$container->setDefinition(ProxyManager::class, $definition);
		}
	}
}