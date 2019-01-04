<?php

/**
 * @file
 * Contains Magnum\Console\Application
 */

namespace Magnum\Console;

use Magnum\Console\CommandLoader\Commands;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Overrides the Symfony Console Application to allow command handlers to be injected in to commands
 * as required.
 *
 * @package Magnum\Console
 */
class Application
	extends \Symfony\Component\Console\Application
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var CommandLoaderInterface
	 */
	protected $commandLoader;

	public function __construct(ContainerInterface $container, ?CommandLoaderInterface $commandLoader = null,
								string $name = 'UNKNOWN', string $version = 'UNKNOWN')
	{
		$this->container = $container;
		parent::__construct($name, $version);

		$commandLoader && $this->setCommandLoader($commandLoader);
	}

	/**
	 * Override to run the register on the commands
	 *
	 * @param CommandLoaderInterface $commandLoader
	 */
	public function setCommandLoader(CommandLoaderInterface $commandLoader)
	{
		if ($commandLoader instanceof Commands) {
			$commandLoader->register($this);
		}

		parent::setCommandLoader($commandLoader);
	}

	/**
	 * Overrides the base doRunCommand to inject Handlers in to commands
	 *
	 * @param Command         $command
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int|void
	 * @throws \Exception
	 * @throws \Throwable
	 */
	protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
	{
		if ($command instanceof CommandConfig) {
			$handler = $command->getHandler();
			if (is_string($handler) && $this->container) {
				$command->setHandler($this->container->get($handler));
			}
		}

		parent::doRunCommand($command, $input, $output);
	}

	/**
	 * Starts a command
	 *
	 * @param string $name
	 * @return CommandConfig
	 */
	public function beginCommand($name)
	{
		$config = new CommandConfig($name, $this);

		$this->add($config);

		return $config;
	}
}