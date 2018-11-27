<?php

namespace Magnum\Container;

use Psr\Container\ContainerInterface;

/**
 * Handles loading the container from the cache or building it from the providers
 *
 * @package Magnum\Container
 */
class Loader
{
	/**
	 * @var string The name of the compiled container
	 */
	const COMPILED_CONTAINER_CLASS = 'CompiledContainer';

	/**
	 * @var string The cache file where the compiled container is stored
	 */
	protected $cacheFile;

	/**
	 * @var bool Whether or not the container is compiled
	 */
	protected $isCompiled     = false;

	/**
	 * @var bool Whether or not to compile the container
	 */
	protected $useCompilation = false;

	/**
	 * @var array List of the providers
	 */
	protected $providers      = [];

	public function __construct($useCompilation = false, $cacheFile = null)
	{
		$this->useCompilation = $useCompilation;
		$this->cacheFile      = $cacheFile;
		if ($cacheFile) {
			$this->isCompiled = file_exists($cacheFile);
		}
	}

	/**
	 * Registers a service provider that will configure the container
	 *
	 * @param $provider
	 * @return $this
	 */
	public function register($provider)
	{
		$this->providers[] = $provider;

		return $this;
	}

	/**
	 * @return ContainerInterface
	 */
	public function load(): ContainerInterface
	{
		if ($this->isCompiled) {
			if (class_exists(static::COMPILED_CONTAINER_CLASS) === false) {
				require $this->cacheFile;
			}

			return new \CompiledContainer;
		}

		$builder = new Builder();

		foreach ($this->resolveProviders() as $provider) {
			$provider->register($builder);
		}

		if ($this->useCompilation && isset($this->cacheFile)) {
			$builder->saveToFile($this->cacheFile, static::COMPILED_CONTAINER_CLASS);
		}

		return $builder->container();
	}

	/**
	 * Loads the providers in to the container
	 */
	protected function resolveProviders(): array
	{
		$providers  = [];
		foreach ($this->providers as $provider) {
			if (is_string($provider)) {
				$provider = new $provider;
			}

			if ($provider instanceof Provider) {
				$providers[] = $provider;
			}
		}

		return $providers;
	}
}
