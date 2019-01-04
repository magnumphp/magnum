<?php

/**
 * @file
 * Contains Magnum\Console\CommandHandler
 */

namespace Magnum\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Contract for CommandHandlers
 *
 * @package Magnum\Console
 */
interface CommandHandler
{
	public function run(InputInterface $input, OutputInterface $output);
}