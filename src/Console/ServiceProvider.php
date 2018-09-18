<?php

namespace Magnum\Console;

use Psr\Container\ContainerInterface;

/**
 * Class ServiceProvider
 *
 * This class needs to remember the state of certain calls to ensure they always return the same objects, these are
 * stored in the instances property
 *
 * @package Magnum\Http
 */
class ServiceProvider
{
	const DEFAULT_APP_NAME = 'Unknown';
	const DEFAULT_APP_VERSION = '0.0.0';
	const APP_NAME_KEY = 'app/name';
	const APP_VERSION_KEY = 'app/version';
	const APP_COMMANDS_KEY = 'app/commands';

	protected $instances = [];

	public function services()
	{
		return [
			self::APP_COMMANDS_KEY => [],
			Application::class     => [$this, 'app']
		];
	}

	public function app(ContainerInterface $container)
	{
		return $this->instance(
			Application::class,
			function () use (&$container) {
				$app = new Application(
					$this->fetchFromContainer($container, self::APP_NAME_KEY, self::DEFAULT_APP_NAME),
					$this->fetchFromContainer($container, self::APP_VERSION_KEY, self::DEFAULT_APP_VERSION),
					$container
				);

				if ($container->has(self::APP_COMMANDS_KEY)) {
					foreach ($container->get(self::APP_COMMANDS_KEY) as $key) {
						$app->add($container->get($key));
					}
				}

				return $app;
			}
		);
	}

	/**
	 * @param ContainerInterface $container
	 * @param string             $key
	 * @param string             $default
	 * @return mixed Returns the value from the container
	 */
	protected function fetchFromContainer(ContainerInterface $container, $key, $default)
	{
		return $container->has($key) ? $container->get($key) : $default;
	}

	protected function instance($key, $callable)
	{
		if (!isset($this->instances[$key])) {
			$this->instances[$key] = $callable();
		}

		return $this->instances[$key];
	}
}