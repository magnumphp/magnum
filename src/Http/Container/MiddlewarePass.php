<?php

namespace Magnum\Http\Container;

use Pipeware\Pipeline\Containerized;
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

		$container->setParameter('http.middleware', array_merge(...$middleware));
		$container->getDefinition(Containerized::class)
				  ->setArgument('$middleware', '%http.middleware%');
	}
}