<?php

namespace Magnum\Fixture\Middleware;

use Magnum\Http\Middleware\IsMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Test
	implements MiddlewareInterface
{
	use IsMiddleware;

	public $did;

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		if ($handler) {
			$this->did = 'handler';

			return $handler->handle($request);
		}

		$this->did = 'response';

		return $this->createResponse(333);
	}
}