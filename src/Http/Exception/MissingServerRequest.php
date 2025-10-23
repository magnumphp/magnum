<?php

/**
 * @file
 * Contains Magnum\Http\Exception\MissingServerRequest
 */

namespace Magnum\Http\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class MissingServerRequest
	extends \InvalidArgumentException
{
	public function __construct($message = "", $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("A " . ServerRequestInterface::class . ' object is required.', $code, $previous);
	}
}