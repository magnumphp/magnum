<?php

/**
 * @file
 * Contains Magnum\Http\Container\MiddlewarePass
 */

namespace Magnum\Http\Container;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Grabs all services that are tagged in the container as "http.middleware" and adds them to the Http\Application
 * middleware stack.
 */
class MiddlewarePass
	implements CompilerPassInterface
{
	const TAG_NAME = 'http.middleware';

	public function process(ContainerBuilder $container)
	{
		$services   = $container->findTaggedServiceIds(self::TAG_NAME, true);
		$middleware = [];
		foreach ($services as $name => $params) {
			$middleware[$params[0][0] ?? 0][] = $name;
		}
		krsort($middleware);

		$container->setParameter(self::TAG_NAME, empty($middleware) ? [] : array_merge(...$middleware));
	}
}