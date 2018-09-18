<?php

namespace Magnum\Http;

use Aura\Di\Container;
use Magnum\Http\Middleware\ExceptionHandler;
use Magnum\Http\Middleware\PipewareFactory;
use Magnum\Http\Middleware\RequestHandler;
use Magnum\Http\Middleware\Responder;
use Magnum\Http\Middleware\Routing;
use Magnum\Http\Routing\Router;
use Middlewares\Utils\Factory;
use Pipeware\Pipeline\Containerized;
use Pipeware\Processor;
use Pipeware\Stack;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Http\Request;

/**
 * Class ServiceProvider
 *
 * This class needs to remember the state of certain calls to ensure they always return the same objects, these are
 * stored in the instances property
 *
 * @package Magnum\Http
 */
class ServiceProvider
{
	const MIDDLEWARE_KEY = 'magnum/middleware';
	const MIDDLEWARE_PROCESSOR_KEY = 'magnum/middleware:processor';
	const MIDDLEWARE_PIPELINE_KEY = 'magnum/middleware:pipeline';
	const APP_MIDDLEWARE_KEY = 'app/middleware';
	const REQUEST_GLOBALS_KEY = 'request/globals';

	protected $instances = [];

	public function services()
	{
		return [
			// configuration
			self::APP_MIDDLEWARE_KEY        => [
				// define our base middleware
				Responder::class,
				ExceptionHandler::class,
				Routing::class,
				RequestHandler::class
			],
			self::REQUEST_GLOBALS_KEY       => $_SERVER,
			// classes
			Application::class              => [$this, 'app'],
			RequestInterface::class         => [$this, 'request'],
			ResponseFactoryInterface::class => [$this, 'responseFactory'],
			PipewareFactory::class          => [$this, 'pipewareFactory'],
			Router::class                   => [$this, 'router'],
			self::MIDDLEWARE_PIPELINE_KEY   => [$this, 'pipewarePipeline'],
			self::MIDDLEWARE_PROCESSOR_KEY  => [$this, 'pipewareProcessor'],
			self::MIDDLEWARE_KEY            => [$this, 'middlewareStack'],

			// middleware
			ExceptionHandler::class         => [$this, 'middlewareErrorHandler'],
			Routing::class                  => [$this, 'middlewareRouting'],
			Responder::class                => [$this, 'middlewareResponder'],
			RequestHandler::class           => [$this, 'middlewareRequestHandler']
		];
	}

	public function app(ContainerInterface $container)
	{
		return $this->instance(
			Application::class,
			function () use (&$container) {
				$app = new Application(
					$container->get(self::MIDDLEWARE_KEY),
					$container->get(RequestInterface::class)
				);

				// inject the high level middleware
				foreach ($container->get(self::APP_MIDDLEWARE_KEY) as $middleware) {
					$app->add($middleware);
				}

				return $app;
			}
		);
	}

	public function request(ContainerInterface $container)
	{
		return $this->instance(
			RequestInterface::class,
			function () use (&$container) {
				$globals = $container->has(self::REQUEST_GLOBALS_KEY) ? $container->get(self::REQUEST_GLOBALS_KEY) : $_SERVER;

				return Request::createFromGlobals($globals);
			}
		);
	}

	public function responseFactory()
	{
		return Factory::getResponseFactory();
	}

	public function pipewareFactory(ContainerInterface $container)
	{
		return new PipewareFactory(
			$container,
			$container->get(ResponseFactoryInterface::class)
		);
	}

	public function pipewarePipeline(ContainerInterface $container)
	{
		return $this->instance(
			Containerized::class,
			function () use (&$container) {
				return new Containerized($container);
			}
		);
	}

	public function pipewareProcessor(ContainerInterface $container)
	{
		return new Processor($container->get(ResponseFactoryInterface::class));
	}

	public function middlewareStack(ContainerInterface $container)
	{
		return new Stack(
			$container->get(self::MIDDLEWARE_PIPELINE_KEY),
			$container->get(self::MIDDLEWARE_PROCESSOR_KEY)
		);
	}

	public function middlewareErrorHandler()
	{
		return new ExceptionHandler();
	}

	public function middlewareResponder()
	{
		return new Responder();
	}

	public function router()
	{
		return $this->instance(
			Router::class,
			function () {
				return new Router();
			}
		);
	}

	public function middlewareRouting(ContainerInterface $container)
	{
		return new Routing($container->get(Router::class));
	}

	public function middlewareRequestHandler(ContainerInterface $container)
	{
		return new RequestHandler(
			$container,
			$container->get(ResponseFactoryInterface::class),
			$container->get(PipewareFactory::class)
		);
	}

	protected function instance($key, $callable)
	{
		if (!isset($this->instances[$key])) {
			$this->instances[$key] = $callable();
		}

		return $this->instances[$key];
	}
}