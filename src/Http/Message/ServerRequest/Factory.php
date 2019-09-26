<?php

namespace Magnum\Http\Message\ServerRequest;

use League\Uri\Http;
use Magnum\Http\Message\ServerRequest;
use Middlewares\Utils\Factory as MiddlewareFactory;
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
		$server  = $globals['_SERVER'] ?? [];
		$uri     = Http::createFromServer($server);
		$factory = MiddlewareFactory::getServerRequestFactory();

		switch (get_class($factory)) {
			case MiddlewareFactory\DiactorosFactory::class:
				$class = DiactorosFactory::class;
				break;
			case MiddlewareFactory\GuzzleFactory::class:
				$class = GuzzleFactory::class;
				break;
			case MiddlewareFactory\SlimFactory::class:
				$class = SlimFactory::class;
				break;
			default:
				throw new \RuntimeException('Unable to create ServerRequest from globals');
		}

		$request = $factory->createServerRequest($server['REQUEST_METHOD'] ?? 'GET', $uri, $server);
		$request = $class::updateRequest($request, $globals)
						 ->withProtocolVersion(str_replace('HTTP/', '', $server['SERVER_PROTOCOL'] ?? '1.1'))
						 ->withCookieParams($globals['_COOKIE'] ?? [])
						 ->withQueryParams($globals['_GET'] ?? []);

		// PSR-7 doesn't have a bulk modifier for headers... and we can't be 100% certain an implementation does
		// @TODO determine the
		foreach ($server as $key => $value) {
			if (!isset($value)) {
				continue;
			}

			if (strpos($key, 'HTTP_') === 0) {
				$header = substr($key, 5);
			}
			elseif (strpos($key, 'CONTENT_') === 0) {
				$header = $key;
			}

			if (isset($header)) {
				$request = $request->withHeader(strtolower(strtr($header, '_', '-')), $value);
				unset($header);
			}
		}

		// Return our decorated ServerRequest
		return new ServerRequest($request);
	}
}