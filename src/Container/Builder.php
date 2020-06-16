<?php

/**
 * @file
 * Contains Magnum\Container\Builder
 */

namespace Magnum\Container;

use Magnum\Container\Compiler\ModifierPass;
use Magnum\Container\Compiler\StaticProxyPass;
use Magnum\ProxyManager\Manager;
use mindplay\filereflection\ReflectionFile;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

/**
 * Builder for Symfony's Dependency Injection container
 *
 * This is basically a wrapper around Symfony's ContainerBuilder to make working with it a little easier.
 *
 * @package Magnum\Container
 */
class Builder
{
	/**
	 * @var ContainerBuilder
	 */
	protected $container;

	/**
	 * @var Finder
	 */
	protected $finder;

	/**
	 * @var array List of proxy classes
	 */
	protected $proxies = [];

	/**
	 * @var array List of modifiers that should occur just before the container is built,
	 *            but after all other registration is complete
	 */
	protected $modifiers = [];

	/**
	 * @var Compiler\ResolveDefaultParameters
	 */
	protected $defaultParametersResolver;

	public function __construct($parameterBag = null)
	{
		$this->container = $this->resolveContainerBuilder($parameterBag);

		$this->addCompilerPass(
			$this->modifiers = new ModifierPass,
			PassConfig::TYPE_BEFORE_OPTIMIZATION,
			0
		);

		// we want this to happen after the defaults are resolved
		$this->addCompilerPass(
			$this->defaultParametersResolver = new Compiler\ResolveDefaultParameters(),
			PassConfig::TYPE_BEFORE_OPTIMIZATION,
			0
		);

		$this->addCompilerPass(
			new StaticProxyPass,
			PassConfig::TYPE_BEFORE_OPTIMIZATION,
			0
		);

		// Ensure full auto-wire is set up
		$this->container->addCompilerPass(
			new Compiler\FullAutowirePass($this->defaultParametersResolver),
			PassConfig::TYPE_OPTIMIZE,
			10
		);
	}

	/**
	 * Proxies the addCompilerPass
	 *
	 * @return Builder
	 */
	public function addCompilerPass(): Builder
	{
		$this->container->addCompilerPass(...func_get_args());

		return $this;
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
		$this->defaultParametersResolver->param('proxies', $this->proxies);

		if ($this->container->isCompiled() === false) {
			$this->container->compile();

			// Ensure the proxies are registered when not compiling
			$this->container->get(Manager::class);
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

		$container = (new PhpDumper($this->container))->dump($this->resolveDumperParameters($class));

		// replace `public function __construct()` with `protected function init()`
		// replace `parent::__construct();` with blankness
		$container = str_replace(
			['public function __construct()', 'parent::__construct();'],
			['protected function init()', ''],
			$container
		);

		file_put_contents($file, $container);
	}

	/**
	 * @param string $class The class to get a reference to
	 *
	 * @return Reference The class reference if it exists, otherwise it creates it
	 */
	public function reference($class)
	{
		if (!$this->container->hasDefinition($class)) {
			$this->register($class);
		}

		return new Reference($class);
	}

	/**
	 * Register an proxy with the system
	 *
	 * @param      $alias
	 * @param      $className
	 */
	public function proxy($alias, $className)
	{
		$this->proxies[$alias] = $className;
	}

	/**
	 * Register's an id as a single instance (shared among calls)
	 *
	 * @param string $target The identifier of the target to alias
	 * @param string $id     The class to be the concrete instance
	 *
	 * @return Alias
	 */
	public function alias(string $target, string $id): Alias
	{
		return $this->container
			->setAlias($id, $target)
			->setPublic(true);
	}

	/**
	 * Creates a Factory definition in the container
	 *
	 * @param string $id     The factory identifier
	 * @param string $class  The class name
	 * @param string $method The method to call on the class
	 *
	 * @return Definition
	 */
	public function factory(string $id, string $class, string $method): Definition
	{
		$definition = (new Definition($class))
			->setFactory(
				[
					$class,
					$method
				]
			);

		$this->container->setDefinition($id, $definition);

		return $definition;
	}

	/**
	 * Returns a list of all classes in the path.
	 *
	 * The classes must be contained in the *.php
	 *
	 * @param string|array $path The path(s) to search for *.php on
	 *
	 * @return array
	 */
	public function findClassesInPath($path): array
	{
		$files   = $this->resolveFinder()->files()->in($path)->name('*.php');
		$classes = [];

		foreach ($files as $file) {
			try {
				$file = new ReflectionFile($file->getRealpath());
				foreach ($file->getClasses() as $class) {
					/** @var \ReflectionClass $class */

					if ($class->isAbstract() || $class->isInterface() || $class->isTrait()) {
						// Don't load these as they can't be instantiated
						continue;
					}

					$classes[] = $class->getName();
				}
			}
			catch (\ReflectionException $e) {
				continue;
			}
		}

		return $classes;
	}

	/**
	 * Returns the definition of the requested ID.
	 *
	 * NOTE: If you need the concrete service call {$builder->container()->get($id)} instead
	 *
	 * @param string $id
	 *
	 * @return Definition
	 */
	public function get(string $id)
	{
		return $this->container->hasDefinition($id) ? $this->container->getDefinition($id) : null;
	}

	/**
	 * Register's an id as shared instance (new on every call)
	 *
	 * @param string $id    The identifier
	 * @param string $class The class name
	 *
	 * @return Definition
	 */
	public function instance(string $id, string $class = null): Definition
	{
		return $this->container
			->autowire($id, $class ?? $id)
			->setAutoconfigured(true)
			->setShared(false);
	}

	/**
	 * Returns whether or not the parameter exists
	 *
	 * @param string $id
	 *
	 * @return bool True if the parameter exists, False otherwise
	 */
	public function hasParameter(string $id): bool
	{
		return $this->container->hasParameter($id);
	}

	/**
	 * Returns the value of the given param
	 *
	 * @param string     $id      The parameter to look up
	 * @param null|mixed $default The default value to return
	 *
	 * @return mixed The parameters value or the $default if it doesn't exist
	 */
	public function getParameter(string $id, $default = null)
	{
		return $this->container->hasParameter($id) ? $this->container->getParameter($id) : $default;
	}

	/**
	 * Sets a parameter
	 *
	 * @param string $id    The parameter id
	 * @param mixed  $value The parameter value
	 *
	 * @return $this
	 */
	public function setParameter($id, $value)
	{
		$this->container->setParameter($id, $value);

		return $this;
	}

	/**
	 * Stores the parameter as a default for when the parameter isn't defined.
	 *
	 * @param string $id    The parameter id
	 * @param mixed  $value The parameter value
	 *
	 * @return self
	 */
	public function setParameterDefault($id, $value): self
	{
		$this->defaultParametersResolver->param($id, $value);

		return $this;
	}

	/**
	 * Merges the parameters in to the existing params
	 *
	 * @param $params
	 *
	 * @return $this
	 */
	public function setParameters($params)
	{
		foreach ($params as $id => $value) {
			$this->container->setParameter($id, $value);
		}

		return $this;
	}

	/**
	 * Register's an id as a single instance (shared among calls)
	 *
	 * @param string $id    The identifier
	 * @param string $class The class name
	 *
	 * @return Definition
	 */
	public function register(string $id, string $class = null): Definition
	{
		return $this->container
			->autowire($id, $class ?? $id)
			->setAutoconfigured(true);
	}

	/**
	 * Registers the child in place of  the parent, and makes the parent the first argument of the child
	 *
	 * NOTE: Child services can be configured from the base class and the FullAutowirePass will handle it for you
	 *
	 * @param string $parent
	 * @param string $child
	 *
	 * @return Definition
	 * @throws \Exception
	 */
	public function decorate(string $parent, string $child): Definition
	{
		$parentDefinition = $this->container->hasDefinition($parent)
			? $this->container->getDefinition($parent)
			: $this->register($parent);

		$this->container->setDefinition($parentKey = "{$child}.parent", $parentDefinition);

		if ($this->container->hasDefinition($child)) {
			$definition = $this->container->getDefinition($child);
			$this->container->setDefinition($parent, $definition);
		}
		else {
			$definition = $this->register($parent, $child);
		}

		if ($parentDefinition->isPublic()) {
			$definition->setPublic(true);
		}

		return $definition->setArgument(0, new Reference($parentKey));
	}

	/**
	 * Convenience function to register a class as a singleton (shared instance)
	 *
	 * @param string $id    The factory identifier
	 * @param string $class The class name
	 *
	 * @return Definition
	 */
	public function singleton(string $id, string $class = null): Definition
	{
		return $this->register($id, $class ?? $id);
	}

	/**
	 * Sets a modifier
	 *
	 * @param string $id The id to modify
	 *
	 * @return Modifier
	 */
	public function modifier(string $id): Modifier
	{
		return $this->modifiers->get($id);
	}

	/**
	 * Resolves the container builder
	 *
	 * @param ParameterBagInterface $parameterBag
	 *
	 * @return ContainerBuilder
	 */
	protected function resolveContainerBuilder(?ParameterBagInterface $parameterBag): ContainerBuilder
	{
		return new ContainerBuilder($parameterBag);
	}

	/**
	 * Resolves the parameters used for dumping the container
	 *
	 * @param string $class
	 *
	 * @return array
	 */
	protected function resolveDumperParameters(string $class): array
	{
		return [
			'class'      => $class,
			'base_class' => CompiledServiceContainer::class,
		];
	}

	/**
	 * Resolves the finder
	 *
	 * @return Finder
	 */
	protected function resolveFinder(): Finder
	{
		return Finder::create();
	}
}