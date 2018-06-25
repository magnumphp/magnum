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

class ErrorHandler
	implements MiddlewareInterface
{
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

		print_r($e->getTraceAsString());
		die($e->getMessage());
		die(get_class($e));

		$headers = ['x-magnum-error' => $e->getMessage()];
		return new Response(500, new Headers($headers));
	}

}