<?php

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Lambda
	implements MiddlewareInterface
{
	protected $lambda;

	public function __construct($lambda)
	{
		$this->lambda = $lambda;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		return call_user_func($this->lambda, $request);
	}
}