<?php

namespace Magnum\Container\Adapter;

use Pimple\Container;

class Pimple
	extends AbstractAdapter
{
	public static function container($rootPath, array $options = [])
	{
		$container = new Container();

		$self = new self($rootPath, $options);
		$self->register($container);

		return $container;
	}
	protected function push($container, $key, $value)
	{
		if (is_callable($value)) {
			$container[$key] = function ($container) use (&$value) {
				return call_user_func($value, $container);
			};
		}
		else {
			$container[$key] = $value;
		}
	}

}