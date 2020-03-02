<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\Responder
 */

namespace Magnum\Http\Middleware;

use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\ResponseEmitter;

/**
 * Middleware to handle sending the response.
 *
 * This wraps the Slim\ResponseEmitter in middleware instead of using it in {\Slim\App::run()}
 */
class Responder
	implements MiddlewareInterface
{
	use IsMiddleware;

	/**
	 * @var ResponseEmitter
	 */
	protected $responseEmitter;

	/**
	 * @var StreamFactoryInterface
	 */
	protected $streamFactory;

	public function __construct(ResponseEmitter $responseEmitter = null)
	{
		$this->responseEmitter = $responseEmitter ?? new ResponseEmitter;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		$response = $handler
			? $handler->handle($request)
			: $this->createResponse(500);

		if (strtoupper($request->getMethod()) === 'HEAD') {
			$response = $response->withBody($this->createEmptyStream());
		}

		return $this->send($response);
	}

	protected function send(ResponseInterface $response): ResponseInterface
	{
		$this->responseEmitter->emit($response);

		return $response;
	}
	/**
	 * Sets the response factory that will be used
	 *
	 * @param StreamFactoryInterface $streamFactory
	 */
	public function setStreamFactory(StreamFactoryInterface $streamFactory)
	{
		$this->streamFactory = $streamFactory;
	}

	/**
	 * Returns the factory created Stream object
	 *
	 * @return StreamInterface The Factory created Response object
	 */
	protected function createEmptyStream(): StreamInterface
	{
		return isset($this->streamFactory)
			? $this->streamFactory->createStream()
			: Factory::createStream();
	}
}