<?php

namespace Magnum\Http;

use Pipeware\Stack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The Magnum HTTP application
 *
 * @package Magnum\Http
 */
class Application
{
	/**
	 * @var Stack
	 */
	protected $middleware;

	/**
	 * @var ServerRequestInterface
	 */
	protected $request;

	public function __construct(Stack $middleware, ?ServerRequestInterface $request = null)
	{
		$this->middleware = $middleware;
		$this->request    = $request;
	}

	/**
	 * Runs the application
	 *
	 * This requires that the request object be provided in the constructor or passed in.
	 *
	 * Based on the middleware that was registered, it's possible the response has already been
	 * sent to the client.
	 *
	 * @param ServerRequestInterface|null $request
	 * @return ResponseInterface The generated response
	 */
	public function run(?ServerRequestInterface $request = null): ResponseInterface
	{
		if (!$request && !$this->request) {
			throw new \InvalidArgumentException("A " . ServerRequestInterface::class . ' object is required.');
		}

		return $this->middleware->handle($request ?: $this->request);
	}
}