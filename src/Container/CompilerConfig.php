<?php

namespace Magnum\Container;

use WoohooLabs\Zen\Config\AbstractCompilerConfig;
use WoohooLabs\Zen\Config\AbstractContainerConfig;
use WoohooLabs\Zen\Config\ContainerConfigInterface;

/**
 * Configuration for the compiled container
 *
 * @package Magnum\Container
 */
class CompilerConfig
	extends AbstractCompilerConfig
{
	/**
	 * @var string The file to write the compiled container to
	 */
	protected $cacheFile;

	/**
	 * @var bool Whether or not annotations should be used
	 */
	protected $annotations = false;

	/**
	 * @var bool Whether or not the container should be compiled
	 */
	protected $compilation = true;

	/**
	 * @var array List of providers
	 */
	protected $providers   = [];

	/**
	 * @var array List of resolved providers
	 */
	protected $resolved;

	public function __construct($cachePath, ...$providers)
	{
		$this->cacheFile = "{$cachePath}/container.php";
		if (count($providers) === 1 && is_array($providers[0])) {
			$this->providers = $providers[0];
		}
		else {
			$this->providers = $providers;
		}
	}

	/**
	 * Loads the container from the cache
	 */
	public function loadFromCache()
	{
		require_once $this->cacheFile;
	}

	/**
	 * Registers a Provider with the compiler config
	 *
	 * @param ContainerConfigInterface $provider
	 * @return CompilerConfig
	 */
	public function register(ContainerConfigInterface $provider): CompilerConfig
	{
		$this->providers[] = $provider;

		return $this;
	}

	/**
	 * Returns whether or not to use annotations
	 *
	 * @return bool
	 */
	public function useAnnotations(): bool
	{
		return $this->annotations;
	}

	/**
	 * Enables annotations
	 *
	 * @return CompilerConfig
	 */
	public function enableAnnotations(): CompilerConfig
	{
		$this->annotations = true;

		return $this;
	}

	/**
	 * Returns whether or not to compile the container
	 *
	 * @return bool
	 */
	public function useCompilation(): bool
	{
		return $this->compilation;
	}

	/**
	 * Disables the compilation of the container
	 *
	 * @return bool
	 */
	public function disableCompilation(): CompilerConfig
	{
		$this->compilation = false;

		return $this;
	}

	/**
	 * Whether or not the cacheFile exists
	 *
	 * @return bool True if the container is compiled, False otherwise
	 */
	public function isCompiled(): bool
	{
		return file_exists($this->cacheFile);
	}

	/**
	 * Saves the container to the cacheFile
	 *
	 * @param $container
	 */
	public function saveCompiled(string $container): void
	{
		file_put_contents($this->cacheFile, $container);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerNamespace(): string
	{
		return 'Magnum\Compiled';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerClassName(): string
	{
		return "Container";
	}

	/**
	 * {@inheritdoc}
	 */
	public function useConstructorInjection(): bool
	{
		return true;
	}

	/**
	 * Ignored because Magnum doesn't use this, but is required by Zen
	 *
	 * Required
	 * @codeCoverageIgnore
	 */
	public function usePropertyInjection(): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContainerConfigs(): array
	{
		if (!isset($this->resolved)) {
			$this->resolved = [];
			foreach ($this->providers as $provider) {
				if ($provider instanceof AbstractContainerConfig) {
					$this->resolved[] = $provider;
				}
				elseif (is_string($provider) && class_exists($provider)) {
					$this->resolved[] = new $provider;
				}
			}
		}

		return $this->resolved;
	}
}