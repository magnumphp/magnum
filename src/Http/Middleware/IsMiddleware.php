<?php

namespace Magnum\Http\Middleware;

use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Middlewares\Utils\Factory;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Response;
use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Trait IsMiddleware
 *
 * @package Magnum\Http\Middleware
 */
trait IsMiddleware
{
	/**
	 * @var ResponseFactoryInterface
	 */
	protected $responseFactory;

	public function setResponseFactory(ResponseFactoryInterface $responseFactory)
	{
		$this->responseFactory = $responseFactory;
	}

	protected function createResponse($code)
	{
		return isset($this->responseFactory)
			? $this->responseFactory->createResponse($code)
			: Factory::createResponse($code);
	}
}