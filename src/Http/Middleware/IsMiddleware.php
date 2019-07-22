<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\IsMiddleware
 */

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;

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

	/**
	 * Sets the response factory that will be used
	 *
	 * @param ResponseFactoryInterface $responseFactory
	 */
	public function setResponseFactory(ResponseFactoryInterface $responseFactory)
	{
		$this->responseFactory = $responseFactory;
	}

	/**
	 * Returns the factory created Response object
	 *
	 * @param int $code The HTTP status code
	 * @return ResponseInterface The Factory created Response object
	 */
	protected function createResponse($code): ResponseInterface
	{
		return isset($this->responseFactory)
			? $this->responseFactory->createResponse($code)
			: Factory::createResponse($code);
	}
}