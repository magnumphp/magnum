<?php

namespace Magnum\Http;

use Middlewares\Utils\Factory\ResponseFactory;
use Pipeware\Pipeline\Basic;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Request;

class Application
{
	/**
	 * @var Stack
	 */
	protected $middleware;

	/**
	 * @var RequestInterface
	 */
	protected $request;

	public function __construct(Stack $middleware, RequestInterface $request = null)
	{
		$this->middleware = $middleware;
		$this->request    = $request;
	}

	public function add($middleware): self
	{
		$this->middleware->push($middleware);

		return $this;
	}

	public function replace($stageName, $replacement): self
	{
		$this->middleware->replace($stageName, $replacement);

		return $this;
	}

	public function remove($stageName)
	{
		$this->middleware->remove($stageName);
	}

	public function run(RequestInterface $request = null): ResponseInterface
	{
		if (!$request && !$this->request) {
			throw new \InvalidArgumentException("A " . RequestInterface::class . ' object is required.');
		}

		return $this->middleware->handle($request ?: $this->request);
	}
}