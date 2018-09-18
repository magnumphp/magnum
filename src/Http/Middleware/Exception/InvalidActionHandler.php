<?php

namespace Magnum\Http\Middleware\Exception;

use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class InvalidActionHandler
	extends \RuntimeException
{
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		$message = sprintf("Invalid action handler, expecting %s, got %s", RequestHandlerInterface::class, $message);
		parent::__construct($message, $code, $previous);
	}
}