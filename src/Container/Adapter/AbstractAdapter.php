<?php

namespace Magnum\Container\Adapter;

use Magnum\Container\Configuration;
use Magnum\Console;
use Magnum\Http;

abstract class AbstractAdapter
{
	const USE_CONFIGURATION = 'useConfiguration';
	protected $rootPath;
	protected $defaultOptions = [
		'useConfiguration' => true,
		'useContainer'     => false,
		'useHttp'          => false,
		'useConsole'       => false
	];

	public function __construct($rootPath, $options = [])
	{
		$this->rootPath = $rootPath;
		$this->options  = array_merge($this->defaultOptions, $options);
	}

	abstract public static function container($rootPath, $options = []);

	public function register($container)
	{
		if ($this->options[self::USE_CONFIGURATION]) {
			$env = new Configuration($this->rootPath);
			foreach ($env->all() as $key => $value) {
				$this->push($container, $key, $value);
			}
		}

		$this->options['useHttp'] && $this->registerHttp($container);
		$this->options['useConsole'] && $this->registerConsole($container);
	}

	protected function registerHttp($container)
	{
		$sp = new Http\ServiceProvider();
		foreach ($sp->services() as $key => $value) {
			$this->push($container, $key, $value);
		}
	}

	protected function registerConsole($container)
	{
		$sp = new Console\ServiceProvider();
		foreach ($sp->services() as $key => $value) {
			$this->push($container, $key, $value);
		}
	}
}