<?php

namespace Magnum\Http\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrackingMiddleware
	implements MiddlewareInterface
{
	public $seen = false;

	public function __construct($response)
	{
		$this->response = $response;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		$this->seen = true;
		if ($handler) {
			return $handler->handle($request);
		}

		return $this->response;
	}

}