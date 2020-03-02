<?php

/**
 * @file
 * Contains Magnum\Http\Message\StaticResponse
 */

namespace Magnum\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Returns a predefined Response
 *
 * @package Magnum\Http\Message
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

	/**
	 * {@inheritDoc}
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->response;
	}
}