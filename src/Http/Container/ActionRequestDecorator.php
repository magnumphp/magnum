<?php

/**
 * @file
 * Contains Magnum\Http\Container\ActionRequestDecorator
 */

namespace Magnum\Http\Container;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Decorates an Action to supply a custom Request Message in to the action
 */
class ActionRequestDecorator
	implements RequestHandlerInterface
{
	/**
	 * @var object The action we are decorating
	 */
	protected $action;

	/**
	 * @var string The FQCN of the decorator
	 */
	protected $decorator;

	public function __construct($action, $decorator)
	{
		$this->action    = $action;
		$this->decorator = $decorator;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->action->handle($this->resolveDecorator($request));
	}

	protected function resolveDecorator(ServerRequestInterface $request)
	{
		if (is_object($this->decorator)) {
			return $this->decorator->setRequest($request);
		}

		$class = $this->decorator;

		return new $class($request);
	}
}