<?php

namespace Magnum\Http;

use Pipeware\Stack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * The Magnum HTTP application
 *
 * @package Magnum\Http
 */
class Application
{
	/**
	 * @var App
	 */
	protected $slim;

	/**
	 * @var ServerRequestInterface
	 */
	protected $request;

	public function __construct(App $slim, ?ServerRequestInterface $request = null)
	{
		$this->slim    = $slim;
		$this->request = $request;
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

		return $this->slim->handle($request ?? $this->request);
	}
}