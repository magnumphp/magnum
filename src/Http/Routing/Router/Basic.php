<?php

namespace Magnum\Http\Routing\Router;

use FastRoute\Dispatcher;
use Magnum\Http\Routing\Result;
use Magnum\Http\Routing\Route;
use Magnum\Http\Routing\Router;
use Psr\Http\Message\RequestInterface;

class Basic
	implements Router
{
	/**
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var array List of named routes
	 */
	protected $namedRoutes = [];

	public function __construct(Dispatcher $dispatcher, $namedRoutes)
	{
		$this->dispatcher  = $dispatcher;
		$this->namedRoutes = $namedRoutes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function match(RequestInterface $request): Result
	{
		$path   = '/' . trim(rawurldecode($request->getUri()->getPath()), '/');
		$result = $this->dispatcher->dispatch(
			$request->getMethod(),
			$path
		);

		if ($result[0] === Dispatcher::FOUND) {
			list($status, list($name, $middleware), $params) = $result;

			return Result::fromRoute(
				new Route($request->getMethod(), $path, $name, $middleware),
				$params
			);
		}

		return Result::fromRouteFailure($path, $result[1] ?? [Route::METHOD_ANY]);
	}
}