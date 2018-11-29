<?php

namespace Magnum\Container;

use Magnum\Container\Exception\InvalidProvider;
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

	/**
	 * @var array List of parameters to inject
	 */
	protected $params = [];

	public function __construct($useCompilation = false, $cacheFile = null)
	{
		$this->useCompilation = $useCompilation;
		$this->cacheFile      = $cacheFile;
		if ($cacheFile) {
			$this->isCompiled = file_exists($cacheFile);
		}
	}

	/**
	 * Returns whether or not the container is compiled.
	 *
	 * @NOTE This is a naive implementation that only checks if the cacheFile exists.
	 *
	 * @return bool
	 */
	public function isCompiled(): bool
	{
		return $this->isCompiled;
	}

	/**
	 * Registers a service provider that will configure the container
	 *
	 * @param $provider
	 * @return $this
	 */
	public function register($provider)
	{
		if (is_string($provider) && class_exists($provider)) {
			$provider = new $provider;
		}

		if (!($provider instanceof Provider)) {
			throw new InvalidProvider($provider);
		}

		$this->providers[] = $provider;

		if (method_exists($provider, 'providers')) {
			foreach ($provider->providers() as $subProvider) {
				$this->register($subProvider);
			}
		}

		return $this;
	}

	/**
	 * Sets a parameter
	 *
	 * @param $id
	 * @param $value
	 * @return $this
	 */
	public function param($id, $value)
	{
		$this->params[$id] = $value;

		return $this;
	}

	/**
	 * Merges the parameters in to the existing params
	 *
	 * @param $params
	 * @return $this
	 */
	public function params($params)
	{
		$this->params = array_merge($this->params ?? [], $params);

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
		$builder->builder()->getParameterBag()->add($this->params);

		foreach ($this->providers as $provider) {
			$provider->register($builder);
		}

		if ($this->useCompilation && isset($this->cacheFile)) {
			$builder->saveToFile($this->cacheFile, static::COMPILED_CONTAINER_CLASS);
		}

		return $builder->container();
	}
}
