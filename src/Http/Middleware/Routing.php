<?php

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Middleware\RoutingMiddleware;

class Routing
	extends RoutingMiddleware
{
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		return $handler->handle($this->performRouting($request));
	}
}