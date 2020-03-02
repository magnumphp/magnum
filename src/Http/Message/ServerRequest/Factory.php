<?php

/**
 * @file
 * Contains Magnum\Http\Message\ServerRequest\Factory
 */

namespace Magnum\Http\Message\ServerRequest;

use GuzzleHttp\Psr7\HttpFactory as GuzzleRequestFactory;
use Laminas\Diactoros\ServerRequestFactory as LaminasRequestFactory;
use Magnum\Http\Message\ServerRequest;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory as SlimRequestFactory;

/**
 * Wraps the Middlewares Utils Factory to create our decorated ServerRequest object
 *
 * This is similar to middlewares/utils in that it attempts to detect which type of PSR-7 library is in use. However, it
 * differs in that we have a specialized createFromGlobals that allows test customization when needed
 *
 * @package Magnum\Http\Message
 */
class Factory
{
	protected static $decorators = [
		LaminasRequestFactory::class => DiactorosFactory::class,
		GuzzleRequestFactory::class  => GuzzleFactory::class,
		SlimRequestFactory::class    => SlimFactory::class
	];

	public static function createFromGlobals(array $globals = []): ServerRequestInterface
	{
		$factory = MiddlewareFactory::getServerRequestFactory();

		if (!($class = self::$decorators[get_class($factory)] ?? null)) {
			throw new \RuntimeException('Unable to create ServerRequest from globals.');
		}

		return new ServerRequest((new $class($factory, $globals))->create());
	}
}