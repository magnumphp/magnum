<?php

namespace Magnum\Container;

use josegonzalez\Dotenv\Loader;
use RuntimeException;
use Psr\Container\ContainerInterface;

class Configuration
	implements ContainerInterface
{
	const DEFAULT_MODE = 'development';
	const KEY_MODE = 'mode';
	const KEY_ROOT_PATH = 'path_root';

	protected $data;
	protected $rootPath;

	public function __construct($rootPath)
	{
		$this->rootPath = $rootPath;
		$this->load();
	}

	public function load($env = null)
	{
		if (is_string($env) || empty($env)) {
			if (!file_exists($env) && !file_exists($env = "{$this->rootPath}/.env")) {
				throw new RuntimeException("Missing .env file");
			}
			$loader = new Loader($env);
			$loader->parse();
			$env = $loader->toArray();
		}

		$this->data = array_merge(
			$env,
			[
				static::KEY_MODE      => $env[static::KEY_MODE] ?: static::DEFAULT_MODE,
				static::KEY_ROOT_PATH => $this->rootPath
			]
		);
	}

	public function get($id)
	{
		if (!array_key_exists($id, $this->data)) {
			throw new \InvalidArgumentException("Parameter not found in .env: $id");
		}

		return $this->data[$id];
	}

	public function has($id)
	{
		return array_key_exists($id, $this->data);
	}
}