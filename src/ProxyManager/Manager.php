<?php

/**
 * @file
 * Contains Magnum\ProxyManager\Manager
 */

namespace Magnum\ProxyManager;

use Psr\Container\ContainerInterface;
use ReStatic\AliasLoader;
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
	 * @var AliasLoaderInterface
	 */
	protected $ourAliasLoader;

	/**
	 * @param ContainerInterface   $container   Container that holds the actual instances
	 * @param AliasLoaderInterface $aliasLoader Alias Loader object that stores and resolves
	 */
	public function __construct(ContainerInterface $container, AliasLoaderInterface $aliasLoader = null)
	{
		parent::__construct($container, $this->ourAliasLoader = $aliasLoader ?? new AliasLoader());

		$this->addProxy('ProxyManager', Proxy::class);
		Proxy::setInstance($this);
	}

	/**
	 * Adds a Static Proxy class by delegating to the Alias Loader
	 *
	 * @param string $alias     Alias to associate with the Static Proxy class
	 * @param string $proxyFqcn FQCN of the Static Proxy class
	 * @param object   $instance  The instance to assign to the proxy
	 *
	 * @return $this
	 */
	public function addProxy($alias, $proxyFqcn, $instance = null): ProxyManager
	{
		if ($instance && method_exists($proxyFqcn, 'setInstance')) {
			$proxyFqcn::setInstance($instance);
		}

		return parent::addProxy($alias, $proxyFqcn);
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
