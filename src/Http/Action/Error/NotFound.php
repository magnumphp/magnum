<?php

namespace Magnum\Http\Action\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response;

class NotFound
	implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return new Response(404);
	}
}