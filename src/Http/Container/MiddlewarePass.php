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
		$services = $container->findTaggedServiceIds(self::TAG_NAME, true);
		$container->setParameter(self::TAG_NAME, $this->sort($services));
	}

	protected function sort($services)
	{
		$after       = [];
		$before      = [];
		$provisional = [];

		foreach ($services as $name => $params) {
			if (isset($params[0]['after'])) {
				$after[$params[0]['after']][] = $name;
			}
			elseif (isset($params[0]['before'])) {
				$after[$params[0]['before']][] = $name;
			}
			else {
				$provisional[$params[0][0] ?? 0][] = $name;
			}
		}

		$middleware = [];
		foreach ($provisional as $idx => $middlewares) {
			foreach ($middlewares as $name) {
				if (isset($after[$name])) {
					foreach ($after[$name] as $other) {
						$middleware[] = $other;
					}
				}

				$middleware[] = $name;
				if (isset($before[$name])) {
					foreach ($before[$name] as $other) {
						$middleware[] = $other;
					}
				}
			}
		}

		return $middleware;
	}
}