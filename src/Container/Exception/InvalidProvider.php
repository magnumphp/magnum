<?php

/**
 * @file
 * Contains Magnum\Container\Exception\InvalidProvider
 */

namespace Magnum\Container\Exception;

use Magnum\Container\Provider;

/**
 * Exception for when a class does not extend the Provider
 *
 * @package Magnum\Container\Exception
 */
class InvalidProvider
	extends \InvalidArgumentException
{
	/**
	 * InvalidProvider constructor.
	 *
	 * @param string|object $received The string or class that was received.
	 */
	public function __construct($received)
	{
		parent::__construct(
			sprintf(
				"Expected '%s' but got '%s'",
				Provider::class,
				is_string($received) ? $received : get_class($received)
			)
		);
	}
}