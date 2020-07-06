<?php

/**
 * @file
 * Contains Magnum\ProxyManager\Manager
 */

namespace Magnum\ProxyManager;

use Psr\Container\ContainerInterface;
use ReStatic\AliasLoaderInterface;
use ReStatic\ProxyManager;

/**
 * The Proxy Manager is the mediator between the Static Proxies, Container, and Alias Loader. It is the main object
 * that the client interacts with at runtime
 */
class Manager
	extends ProxyManager
{
	/**
	 * @param ContainerInterface   $container   Container that holds the actual instances
	 * @param AliasLoaderInterface $aliasLoader Alias Loader object that stores and resolves
	 */
	public function __construct(ContainerInterface $container, AliasLoaderInterface $aliasLoader = null)
	{
		parent::__construct($container, $aliasLoader);

		$this->addProxy('ProxyManager', Proxy::class, $this);
	}

	/**
	 * Factory method for the DependencyInjection Container
	 *
	 * @param ContainerInterface        $container
	 * @param AliasLoaderInterface|null $aliasLoader
	 * @param array                     $proxies
	 *
	 * @return Manager
	 */
	public static function factory(
		ContainerInterface $container,
		AliasLoaderInterface $aliasLoader = null,
		array $proxies = []
	) {
		$instance = new self($container, $aliasLoader);
		$instance->enable(self::ROOT_NAMESPACE_ANY);

		$instance->setContainer($container);

		foreach ($proxies as $alias => $target) {
			$instance->addProxy($alias, $target);
		}

		return $instance;
	}
}
