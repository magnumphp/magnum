<?php

/**
 * @file
 * Contains Magnum\Console\CommandProvider
 */

namespace Magnum\Console;

/**
 * Contract for Command Providers
 *
 * @package Magnum\Console
 */
interface CommandProvider
{
	/**
	 * @param CommandConfig $config
	 * @return mixed
	 */
	public static function configure(CommandConfig $config);
}