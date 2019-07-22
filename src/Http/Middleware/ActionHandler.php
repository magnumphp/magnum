<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\ActionHandler
 */

namespace Magnum\Http\Middleware;

use Magnum\Http\Routing\Route;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ADR Request Handler
 *
 * This is in most cases the last of the middleware
 *
 * @package Magnum\Http\Middleware
 */
class ActionHandler
	implements MiddlewareInterface
{
	use IsMiddleware;

	/**
	 * @var PipewareFactory
	 */
	protected $pipewareFactory;

	public function __construct(PipewareFactory $pipewareFactory, ResponseFactoryInterface $responseFactory)
	{
		$this->pipewareFactory = $pipewareFactory;
		$this->responseFactory = $responseFactory;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		/** @var Route $route */
		$route = $request->getAttribute(Routing::ATTRIBUTE);
		if (empty($route)) {
			return $this->createResponse(404);
		}

		return $this->pipewareFactory
			->newContainerInstance($route->middleware())
			->handle($request);
	}
}