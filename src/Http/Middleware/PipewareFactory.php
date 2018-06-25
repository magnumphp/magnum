<?php

namespace Magnum\Http\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Pipeware\Pipeline\Basic;
use Pipeware\Pipeline\Containerized;
use Pipeware\Pipeline\Pipeline;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Container\ContainerInterface;

class PipewareFactory
{
	private $container;
	private $responseFactory;

	public function __construct(ContainerInterface $container, ResponseFactoryInterface $responseFactory)
	{
		$this->container       = $container;
		$this->responseFactory = $responseFactory;
	}

	public function newBasicInstance($middlewares = []): Stack
	{
		$list = $this->container->has(Basic::class)
			? $this->container->get(Basic::class)
			: new Basic();

		return $this->buildStack($list, $middlewares);
	}

	public function newContainerInstance($middlewares = []): Stack
	{
		$list = $this->container->has(Containerized::class)
			? $this->container->get(Containerized::class)
			: new Containerized($this->container);

		return $this->buildStack($list, $middlewares);
	}

	public function newStack(Pipeline $pipeline, $processor = null): Stack
	{
		if (!$processor) {
			if ($this->container->has(Processor::class)) {
				$processor = $this->container->get(Processor::class);
			}
			else {
				$processor = new Processor($this->responseFactory);
			}
		}

		return new Stack($pipeline, $processor);
	}

	protected function buildStack(Pipeline $list, $middlewares = []): Stack
	{
		foreach ($middlewares as $middleware) {
			$list = $list->pipe($middleware);
		}

		return $this->newStack($list);
	}
}