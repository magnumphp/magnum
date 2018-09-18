<?php

namespace Magnum\Http\Middleware;

use Exception;
use Slim\Http\Headers;
use Slim\Http\Response;
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

	protected $sendExceptionMessage = false;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			return $handler->handle($request);
		}
		catch (Exception $e) {
			return $this->handleException($request, $e);
		}
		catch (Throwable $e) {
			return $this->handleException($request, $e);
		}
	}

	protected function handleException($request, $e)
	{
		if (isset($this->logger)) {
			$this->logger->error($e);
		}

		$response = $this->responseFactory->createResponse(500, "There was an error processing your request");

		if ($this->sendExceptionMessage) {
			$response = $response->withHeader('x-magnum-error', $e->getMessage());
		}
		return $response;
	}
}