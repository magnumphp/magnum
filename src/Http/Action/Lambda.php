<?php

namespace Magnum\Http\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Lambda
	implements RequestHandlerInterface
{
	protected $lambda;

	public function __construct($lambda)
	{
		$this->lambda = $lambda;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return call_user_func($this->lambda, $request);

	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
	}
}