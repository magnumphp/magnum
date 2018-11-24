<?php

namespace Magnum\Container;

use Psr\Container\ContainerInterface;
use WoohooLabs\Zen\Container\Compiler;

class Container
	implements ContainerInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var CompilerConfig
	 */
	protected $config;

	public function __construct(CompilerConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * Builds the concrete Zen container
	 *
	 * @return ContainerInterface
	 */
	public function build($debug = false): ContainerInterface
	{
		if ($this->config->useCompilation()) {
			$this->config->isCompiled() || $this->config->saveCompiled($this->resolveContainer());
			$this->config->loadFromCache();
		}
		else {
			eval(substr($this->resolveContainer(), 5));
		}

		$class = $this->config->getContainerFqcn();

		$this->container = new $class;

		return $this->container;
	}

	public function get($id)
	{
		return $this->container->get($id);
	}

	public function has($id)
	{
		return $this->container->has($id);
	}

	protected function resolveContainer()
	{
		$resolver = new DependencyResolver($this->config);
		$resolver->resolveEntryPoints();

		$compiler          = new Compiler();
		$compiledContainer = $compiler->compile($this->config, $resolver->getDefinitions());

		return $compiledContainer;
	}
}