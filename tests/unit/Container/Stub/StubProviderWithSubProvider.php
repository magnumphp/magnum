<?php

namespace Magnum\Container\Stub;

use Magnum\Container\Builder;
use Magnum\Container\Provider;

class StubProviderWithSubProvider
	implements Provider
{
	public function providers()
	{
		return [
			StubProvider::class
		];
	}

	public function register(Builder $builder)
	{
		$builder->setParameter('stub-with-sub', true);
	}
}