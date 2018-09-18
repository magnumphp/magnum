<?php

namespace Magnum\Container\Adapter;

use Aura\Di\Container;
use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Resolver\Reflector;
use Aura\Di\Resolver\Resolver;
use Magnum\Container\AuraContainer;
use Magnum\Container\Resolver\ContextResolver;

class AuraDi
	extends AbstractAdapter
{
	/**
	 * Creates a container and registers this adapter
	 */
	public static function container($rootPath, $options = [], $resolver = null)
	{
		$resolver  = $resolver ?: new ContextResolver(new Reflector());
		$container = new Container(
			new InjectionFactory($resolver)
		);
		$container->set(Resolver::class, $resolver);

		$container = new AuraContainer($container);
		$self      = new self($rootPath, $options);
		$self->register($container);

		return $container;
	}

	protected function push($container, $key, $value)
	{
		if (is_callable($value)) {
			$func = function () use (&$container, &$value) {
				return call_user_func($value, $container);
			};
			$container->set($key, $func);
		}
		else {
			$container->values[$key] = $value;
		}
	}
}