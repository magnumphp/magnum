<?php

/**
 * @file
 * Contains Magnum\Console\CommandLoader\Commands
 */

namespace Magnum\Console\CommandLoader;

use Magnum\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

/**
 * This class enables us to load commands in to the Application and cache some of the basic information for
 * faster usage on the console.
 *
 * @package Magnum\Console\CommandLoader\Container
 */
class Commands
	implements CommandLoaderInterface
{
	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var array List of commands. Loaded from the file
	 */
	protected $commands = [];

	/**
	 * @var array List of actual classes
	 */
	protected $classes = [];

	/**
	 * @var bool Whether the commands were loaded from a file
	 */
	protected $loaded = false;

	public function __construct(string $path, $commands)
	{
		$this->classes = $commands;

		if (!empty($path) && file_exists($path)) {
			$this->file    = "{$path}/_commands.php";
			if (file_exists($this->file)) {
				$this->loaded   = true;
				$this->commands = require $this->file;
			}
		}
	}

	/**
	 * Save the commands if not loaded from file
	 */
	public function __destruct()
	{
		if ($this->loaded !== true && $this->file) {
			file_put_contents($this->file, '<?php return ' . var_export($this->commands, true) . ';');
		}
	}

	/**
	 * Registers all the classes with the application
	 *
	 * @param Application $app
	 */
	public function register(Application $app)
	{
		if (empty($this->commands)) {
			foreach ($this->classes as $class) {
				$config = $app->beginCommand($class);
				$class::configure($config);
				$this->commands[$config->getName()] = $class;
			}
		}
	}

	public function get(string $name): Command
	{
		if (empty($this->commands[$name])) {
			throw new \InvalidArgumentException("Command does not exist: $name");
		}

		return new CommandProxy($name, $this->commands[$name]);
	}

	public function has(string $name): bool
	{
		return isset($this->commands[$name]);
	}

	public function getNames(): array
	{
		return array_keys($this->commands);
	}
}