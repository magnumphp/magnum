<?php

/**
 * @file
 * Contains Magnum\Console\CommandConfig
 */

namespace Magnum\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console Command Configuration
 *
 * @package Magnum\Console
 */
class CommandConfig
	extends \Symfony\Component\Console\Command\Command
{
	/**
	 * @var CommandHandler|callable|string
	 */
	protected $handler;

	/**
	 * @var Application
	 */
	protected $app;

	public function __construct($name, ?Application $app)
	{
		parent::__construct($name);
		$this->app  = $app;
	}

	/**
	 * Sets the handler to be used
	 *
	 * @param $handler
	 * @return $this
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Returns the existing handler
	 *
	 * @return string|callable|CommandHandler
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Ends the command configuration
	 *
	 * @return Application
	 */
	public function end()
	{
		return $this->app;
	}

	/**
	 * Executes the command handler
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		if ($this->handler instanceof CommandHandler) {
			return $this->handler->run($input, $output);
		}
		elseif (is_callable($this->handler)) {
			return call_user_func($this->handler, $input, $output);
		}

		throw new Exception\InvalidCommandHandler($this->handler);
	}
}