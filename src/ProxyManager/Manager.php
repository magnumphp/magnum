<?php

namespace Magnum\ProxyManager;

use Psr\Container\ContainerInterface;
use XStatic\AliasLoader;
use XStatic\AliasLoaderInterface;

/**
 * The Proxy Manager is the mediator between the Static Proxies, Container, and Alias Loader. It is the main object
 * that the client interacts with at runtime
 */
class Manager
{
	const ROOT_NAMESPACE_GLOBAL = false;
	const ROOT_NAMESPACE_ANY = true;

	/**
	 * @var Manager
	 */
	protected static $instance;

	/**
	 * @var ContainerInterface Container to inject into the Static Proxy classes and that holds the actual instances
	 */
	private $container;

	/**
	 * @var AliasLoaderInterface Alias Loader that resolves aliases to their corresponding Static Proxy classes
	 */
	private $aliasLoader;

	/**
	 * @param ContainerInterface   $container   Container that holds the actual instances
	 * @param AliasLoaderInterface $aliasLoader Alias Loader object that stores and resolves
	 */
	public function __construct(ContainerInterface $container, AliasLoaderInterface $aliasLoader = null)
	{
		$this->container   = $container;
		$this->aliasLoader = $aliasLoader ?: new AliasLoader();

		$this->addProxy('ProxyManager', Proxy::class, $this);
	}

	/**
	 * Enables the Static Proxies by injecting the Container object and registering the Alias Loader for autoloading
	 *
	 * @param bool|string $rootNamespace The namespace that the alias should be created in
	 *
	 * @return bool
	 * @see \XStatic\AliasLoaderInterface::register()
	 */
	public function enable($rootNamespace = self::ROOT_NAMESPACE_GLOBAL): bool
	{
		// If XStatic is already enabled, this is a no-op
		if ($this->aliasLoader->isRegistered()) {
			return true;
		}

		// Register the loader to handle aliases and link the proxies to the container
		if ($this->aliasLoader->register($rootNamespace)) {
			StaticProxy::setContainer($this->container);
		}

		return $this->aliasLoader->isRegistered();
	}

	/**
	 * Adds a Static Proxy class by delegating to the Alias Loader
	 *
	 * @param string $alias     Alias to associate with the Static Proxy class
	 * @param string $proxyFqcn FQCN of the Static Proxy class
	 * @param object $instance  The instance to assign to the proxy
	 *
	 * @return $this
	 */
	public function addProxy($alias, $proxyFqcn, $instance = null): self
	{
		if ($instance && method_exists($proxyFqcn, 'setInstance')) {
			$proxyFqcn->setInstance($instance);
		}

		$this->aliasLoader->addAlias($alias, $proxyFqcn);

		return $this;
	}

	/**
	 * Sets the Container object that provides the actual subjects' instances
	 *
	 * @param ContainerInterface $container Instance of a Container (or Service Locator)
	 *
	 * @return $this
	 */
	public function setContainer(ContainerInterface $container): self
	{
		$this->container = $container;
		StaticProxy::setContainer($this->container);

		return $this;
	}

	/**
	 * Performs the proxying of the statically called method to the Proxy Subject in the Container
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		return call_user_func_array(array(static::$instance, $method), $args);
	}
}
