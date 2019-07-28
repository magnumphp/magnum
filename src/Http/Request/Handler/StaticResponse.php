<?php

/**
 * @file
 * Contains Magnum\Http\RequestHandler\StaticResponse
 */

namespace Magnum\Http\Request\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Returns a predefined Response
 *
 * @package Magnum\Http\RequestHandler
 */
class StaticResponse
	implements RequestHandlerInterface
{
	/**
	 * @var ResponseInterface
	 */
	protected $response;

	public function __construct(ResponseInterface $response)
	{
		$this->response = $response;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->response;
	}
}