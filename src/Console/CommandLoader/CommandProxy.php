<?php

/**
 * @file
 * Contains Magnum\Console\CommandLoader\CommandProxy
 */

namespace Magnum\Console\CommandLoader;

use Magnum\Console\CommandConfig;
use Magnum\Console\CommandHandler;

/**
 * Due to the way we load commands (via the begin/end on the app), we proxy them here so that the DI add() method works
 * properly with the type hints
 *
 * @package Magnum\Console\CommandLoader
 */
class CommandProxy
	extends CommandConfig
{
	/**
	 * @var CommandHandler|callable|string
	 */
	protected $configClass;

	public function __construct($name, $configClass)
	{
		$this->configClass = $configClass;
		parent::__construct($name, null);
	}

	protected function configure()
	{
		($this->configClass)::configure($this);
	}
}