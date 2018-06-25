<?php

namespace Magnum\Http\Routing;

use Interop\Http\Factory\ResponseFactoryInterface;
use Magnum\Http\Middleware\PipewareFactory;
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
 * @TODO    Look at making a Pipeware Factory so we aren't instantiating the Stack on it's own...
 *
 * @package Magnum\Http\Route
 */
class Route
	implements RequestHandlerInterface
{
	private $route;
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var PipewareFactory
	 */
	private $pipewareFactory;

	public function __construct(ContainerInterface $container, PipewareFactory $pipewareFactory)
	{
		$this->container       = $container;
		$this->pipewareFactory = $pipewareFactory;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		/** @var \Slim\Route $route */
		$route = $request->getAttribute('route');

		// we replace the __invoke with our own processing to be PSR-15 compliant
		$callable = $route->getCallable();
		if (is_string($callable)) {
			$action = $this->container->get($callable);
		}

		if (!($action instanceof RequestHandlerInterface)) {
			throw new \RuntimeException("Invalid action handler, expecting " . RequestHandlerInterface::class . ', got ' . get_class($action));
		}

		$route->finalize();

		$middleware   = $route->getMiddleware();
		$middleware[] = $action; // push the action to the end of the middleware

		return $this->pipewareFactory->newContainerInstance($middleware)->handle($request);
	}
}