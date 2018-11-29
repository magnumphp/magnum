<?php

namespace Magnum\Container\Stub;

use Magnum\Container\Builder;
use Magnum\Container\Provider;

class StubProvider
	implements Provider
{

	public function register(Builder $builder)
	{
		$builder->param('stub', true);
	}
}