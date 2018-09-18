<?php

namespace Magnum\Http\Routing;

use Psr\Http\Message\ResponseFactoryInterface;
use Magnum\Http\Middleware\PipewareFactory;
use Magnum\ProxyManager\WrapsObject;
use Pipeware\Pipeline\Containerized;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response;

/**
 * Wraps the Slim Route to make it PSR-15 compatible
 *
 * @package Magnum\Http\Route
 */
class Route
{
	use WrapsObject;

	protected $middleware = [];

	public function __construct($methods, string $pattern, $callable, $groups = [], int $identifier = 0)
	{
		$this->instance = new \Slim\Route($methods, $pattern, $callable, $groups, $identifier);
	}

	public function add($middleware)
	{
		$this->middleware[] = $middleware;
	}

	public function getMiddleware()
	{
		return array_merge($this->middleware, $this->instance->getMiddleware());
	}
}