<?php

namespace Magnum\Container;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * Builder for Symfony's Dependency Injection container
 *
 * This is basically a wrapper around Symfony's ContainerBuilder to make working with it a little easier.
 *
 * @NOTE This marks all instances as public
 *
 * @package Magnum\Container
 */
class Builder
{
	/**
	 * @var ContainerBuilder
	 */
	protected $container;

	public function __construct()
	{
		$this->container = new ContainerBuilder;

		// Ensure full auto-wire is set up
		$config = $this->container->getCompiler()->getPassConfig();
		$config->addPass(
			new Compiler\FullAutowirePass(),
			PassConfig::TYPE_OPTIMIZE,
			10
		);
	}

	/**
	 * Returns the ContainerBuilder instance.
	 *
	 * The {self::container()} method handles compiling the container if needed
	 *
	 * @return ContainerBuilder
	 */
	public function builder(): ContainerBuilder
	{
		return $this->container;
	}

	/**
	 * Returns the compiled container
	 *
	 * Unlike the {self::builder()} method this will compile the container if it isn't already compiled.
	 *
	 * @return ContainerInterface
	 */
	public function container(): ContainerInterface
	{
		if ($this->container->isCompiled() === false) {
			$this->container->compile();
		}

		return $this->container;
	}

	/**
	 * Saves the container to give file, with the given class name
	 *
	 * @param string $file  The file to write the compiled container too
	 * @param string $class The class name to use for the compiled container
	 */
	public function saveToFile($file, $class)
	{
		$this->container();

		file_put_contents(
			$file,
			(new PhpDumper($this->container))->dump(
				[
					'class' => $class
				])
		);
	}

	// services

	/**
	 * Creates a Factory definition in the container
	 *
	 * @param string $id     The factory identifier
	 * @param string $class  The class name
	 * @param string $method The method to call on the class
	 * @return Definition
	 */
	public function factory(string $id, string $class, string $method): Definition
	{
		$definition = (new Definition($class))
			->setFactory(
				[
					$class,
					$method
				]);
		$definition->setPublic(true);

		$this->container->setDefinition($id, $definition);

		return $definition;
	}

	/**
	 * Convenience function to register a class as a singleton (shared instance)
	 *
	 * @param string $id    The factory identifier
	 * @param string $class The class name
	 * @return Definition
	 */
	public function singleton(string $id, string $class = null): Definition
	{
		return $this->register($id, $class ?? $id);
	}

	/**
	 * Register's an id as shared instance (new on every call)
	 *
	 * @param string $id    The identifier
	 * @param string $class The class name
	 * @return Definition
	 */
	public function instance(string $id, string $class = null): Definition
	{
		return $this->container
			->autowire($id, $class ?? $id)
			->setAutoconfigured(true)
			->setPublic(true)
			->setShared(false);
	}

	/**
	 * Register's an id as a single instance (shared among calls)
	 *
	 * @param string $id    The identifier
	 * @param string $class The class name
	 * @return Definition
	 */
	public function register(string $id, string $class = null): Definition
	{
		return $this->container
			->autowire($id, $class ?? $id)
			->setAutoconfigured(true)
			->setPublic(true);
	}

	/**
	 * Register's an id as a single instance (shared among calls)
	 *
	 * @param string $target The identifier of the target to alias
	 * @param string $id     The class to be the concrete instance
	 * @return Alias
	 */
	public function alias(string $target, string $id): Alias
	{
		return $this->container
			->setAlias($id, $target)
			->setPublic(true);
	}
}