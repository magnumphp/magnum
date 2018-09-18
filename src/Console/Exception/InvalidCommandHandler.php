<?php

/**
 * @file
 * Contains Magnum\Console\Exception\InvalidCommandHandler
 */

namespace Magnum\Console\Exception;

use Magnum\Console\CommandHandler;

class InvalidCommandHandler
	extends \RuntimeException
{
	public function __construct($handler)
	{
		$this->message = "Handler must be a callable or instanceof " . CommandHandler::class . ". Received " .
			(is_object($handler) ? get_class($handler) : gettype($handler));
	}
}