<?php

/**
 * @file
 * Contains Magnum\Http\Application
 */

namespace Magnum\Http;

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

	/**
	 * @var array List of middleware for the Application
	 */
	protected $middleware = [];

	public function __construct(App $slim, $middleware = [], ?ServerRequestInterface $request = null)
	{
		$this->slim       = $slim;
		$this->middleware = array_filter($middleware);
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
	 *
	 * @return ResponseInterface The generated response
	 */
	public function run(?ServerRequestInterface $request = null): ResponseInterface
	{
		if (!$request && !$this->request) {
			throw new Exception\MissingServerRequest();
		}

		foreach ($this->middleware as $middleware) {
			$this->slim->add($middleware);
		}

		return $this->slim->handle($request ?? $this->request);
	}
}