<?php

/**
 * @file
 * Contains Magnum\Console\Container\ConsoleCommandPass
 */

namespace Magnum\Console\Container;

use Magnum\Console\CommandLoader\Commands;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Grabs all services that are tagged in the container as "console.command" and sets them as the $commands argument
 * for the Commands CommandLoader
 *
 * @package Magnum\Console\Container
 */
class ConsoleCommandPass
	implements CompilerPassInterface
{
	const TAG_NAME = 'console.command';

	public function process(ContainerBuilder $container)
	{
		$services = $container->findTaggedServiceIds(self::TAG_NAME, true);

		$container->setParameter('console.commands', array_keys($services));
		$container->getDefinition(Commands::class)
				  ->setArgument('$path', '%path%/data/bootstrap')
				  ->setArgument('$commands', '%console.commands%');
	}
}