<?php

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Route;

/**
 * ADR Request Handler
 *
 * This is in most cases the last of the middleware
 *
 * @package Magnum\Http\Middleware
 */
class RequestHandler
	implements MiddlewareInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var PipewareFactory
	 */
	private $pipewareFactory;

	public function __construct(ContainerInterface $container,
								ResponseFactoryInterface $responseFactory,
								PipewareFactory $pipewareFactory)
	{
		$this->container       = $container;
		$this->responseFactory = $responseFactory;
		$this->pipewareFactory = $pipewareFactory;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler = null): ResponseInterface
	{
		/** @var Route $route */
		$route = $request->getAttribute('route');
		if (empty($route)) {
			return $this->responseFactory->createResponse(404);
		}

		$callable = $route->getCallable();
		if (is_string($callable)) {
			$action = $this->container->get($callable);
		}

		if (!($action instanceof RequestHandlerInterface)) {
			throw new \RuntimeException("Invalid action handler, expecting " . RequestHandlerInterface::class . ', got ' . get_class($action));
		}

		$route->finalize();

		$middleware   = $route->getMiddleware();
		if (empty($middleware)) {
			return $action->handle($request);
		}

		$middleware[] = new ActionHandler($action);

		return $this->pipewareFactory->newContainerInstance($middleware)->handle($request);
	}
}