<?php

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\RouteInterface;
use Slim\Route;

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
	/**
	 * @var RequestHandlerInterface
	 */
	protected $action;

	public function __construct($action)
	{
		$this->action = $action;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		return $this->action->handle($request);
	}
}