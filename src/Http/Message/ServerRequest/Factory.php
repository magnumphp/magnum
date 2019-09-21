<?php

namespace Magnum\Http\Message\ServerRequest;

use League\Uri\Http;
use Magnum\Http\Message\ServerRequest;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Wraps the Middlewares Utils Factory to more easily create the ServerRequest object
 *
 * This is similar to middlewares/utils in that it attempts to detect which type of PSR-7 library is in use. However, it
 * differs in that we have a specialized createFromGlobals that allows full test customization when needed
 *
 * @package Magnum\Http\Message
 */
class Factory
{
	public static function createFromGlobals(array $globals = null): ServerRequestInterface
	{
		$globals = $globals ?? $GLOBALS;
		$factory = MiddlewareFactory::getServerRequestFactory();
		$server  = $globals['_SERVER'] ?? [];
		$request = $factory
			->createServerRequest($server['REQUEST_METHOD'] ?? 'GET', Http::createFromServer($server), $server)
			->withProtocolVersion(str_replace('HTTP/', '', $server['SERVER_PROTOCOL'] ?? '1.1'))
			->withCookieParams($globals['_COOKIE'] ?? [])
			->withQueryParams($globals['_GET'] ?? []);

		if ($factory instanceof MiddlewareFactory\DiactorosFactory) {
			$request = DiactorosFactory::updateRequest($request, $globals);
		}
		elseif ($factory instanceof MiddlewareFactory\GuzzleFactory) {
			$request = GuzzleFactory::updateRequest($request, $globals);
		}
		// we can't use SlimFactory as this is Slim-Http
		elseif ($factory instanceof MiddlewareFactory\SlimFactory) {
			$request = SlimFactory::updateRequest($request, $globals);
		}
		else {
			throw new \RuntimeException('Unable to create ServerRequest from globals');
		}

		return new ServerRequest($request);
	}
}