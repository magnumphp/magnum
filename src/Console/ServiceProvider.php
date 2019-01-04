<?php

/**
 * @file
 * Contains Magnum\Console\ServiceProvider
 */

namespace Magnum\Console;

use Magnum\Console\CommandLoader\Commands;
use Magnum\Console\Container\ConsoleCommandPass;
use Magnum\Container\Builder;
use Magnum\Container\Provider;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A basic service provider for the Magnum Container Builder that sets up the basic console
 *
 * @package Magnum\Http
 */
class ServiceProvider
	implements Provider
{
	const DEFAULT_APP_NAME    = 'Unknown';
	const DEFAULT_APP_VERSION = '0.0.0';
	const APP_NAME_KEY        = 'app_name';
	const APP_VERSION_KEY     = 'app_version';

	public function register(Builder $builder)
	{
		$builder->setParameterDefault(self::APP_NAME_KEY, static::DEFAULT_APP_NAME);
		$builder->setParameterDefault(self::APP_VERSION_KEY, static::DEFAULT_APP_VERSION);
		$builder->register(Application::class)
				->setArguments(
					[
						'$commandLoader' => new Reference(Commands::class),
						'$name'          => new Parameter(self::APP_NAME_KEY),
						'$version'       => new Parameter(self::APP_VERSION_KEY)
					]
				);

		$builder->register(Commands::class);

		$builder->addCompilerPass(new ConsoleCommandPass());
	}
}