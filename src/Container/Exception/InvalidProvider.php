<?php

namespace Magnum\Container\Exception;

use Magnum\Container\Provider;

class InvalidProvider
	extends \InvalidArgumentException
{
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