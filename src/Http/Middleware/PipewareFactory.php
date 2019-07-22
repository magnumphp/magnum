<?php

/**
 * @file
 * Contains Magnum\Http\Middleware\PipewareFactory
 */

namespace Magnum\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Pipeware\Pipeline\Basic;
use Pipeware\Pipeline\Containerized;
use Pipeware\Pipeline\Pipeline;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Builds the Pipeware Middleware Stack based on the specified middleware
 *
 * @package Magnum\Http\Middleware
 */
class PipewareFactory
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var ResponseFactoryInterface
	 */
	private $responseFactory;

	public function __construct(ContainerInterface $container, ResponseFactoryInterface $responseFactory)
	{
		$this->container       = $container;
		$this->responseFactory = $responseFactory;
	}

	public function newBasicInstance($middlewares = []): Stack
	{
		$pipeline = $this->container->has(Basic::class)
			? $this->container->get(Basic::class)
			: new Basic();

		return $this->buildStack($pipeline, $middlewares);
	}

	public function newContainerInstance($middlewares = []): Stack
	{
		$pipeline = $this->container->has(Containerized::class)
			? $this->container->get(Containerized::class)
			: new Containerized($this->container);

		return $this->buildStack($pipeline, $middlewares);
	}

	/**
	 * Returns the stack object
	 *
	 * @param Pipeline $pipeline
	 * @param null     $processor
	 * @return RequestHandlerInterface
	 */
	public function newStack(Pipeline $pipeline, $processor = null): Stack
	{
		$processor = $processor ?? ($this->container->has(Processor::class)
				? $this->container->get(Processor::class)
				: new Processor($this->responseFactory));

		return new Stack($pipeline, $processor);
	}

	/**
	 * Builds the pipeline from the middleware and then sets it in the stack
	 *
	 * @param Pipeline $pipeline
	 * @param array    $middlewares
	 * @return RequestHandlerInterface
	 */
	protected function buildStack(Pipeline $pipeline, $middlewares = []): Stack
	{
		foreach ($middlewares as $middleware) {
			$pipeline = $pipeline->pipe($middleware);
		}

		return $this->newStack($pipeline);
	}
}