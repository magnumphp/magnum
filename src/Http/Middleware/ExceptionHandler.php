<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\ExceptionHandler
 */

namespace Magnum\Http\Middleware;

use Exception;
use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This class is here to handle any exceptions that bubble up from the system itself.
 *
 * ExceptionHandler should be used to render any 404's or other views that may want to be rendered
 *
 * @package Magnum\Http\Middleware
 */
class ExceptionHandler
	implements MiddlewareInterface
{
	use IsMiddleware;

	/**
	 * @var bool Whether or not the exceptions message should be passed in the header
	 */
	protected $sendExceptionMessage = false;

	public function __construct($sendExceptionMessage = false)
	{
		$this->sendExceptionMessage = $sendExceptionMessage;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			$response = $handler->handle($request);
		}
		catch (Exception | Throwable $e) {
			$response = $this->createResponse(500)->withStatus(500, "There was an error processing your request");

			if ($this->sendExceptionMessage) {
				$response = $response->withHeader('x-magnum-error', $e->getMessage());
			}
		}

		return $response;
	}
}