<?php

namespace Magnum\Container\Adapter;

use League\Container\Container;
use League\Container\ContainerInterface;
use League\Container\ServiceProvider\ServiceProviderInterface;

class League
	extends AbstractAdapter
{
	public static function container($rootPath, $options = [])
	{
		$container = new Container();
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
			$container->add($key, $func);
		}
		else {
			$container->add($key, $value);
		}
	}
}