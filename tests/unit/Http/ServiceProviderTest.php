<?php

namespace Magnum\Http;

use Magnum\Container\Builder;
use Magnum\Http\Fixture\TestRoutes;
use Magnum\Http\Stub\Routes;
use PHPUnit\Framework\TestCase;
use Slim\Turbo\Provider\RouteProvider;

class ServiceProviderTest
	extends TestCase
{

	public function testRegistration()
	{
		$builder = new Builder();
		(new ServiceProvider())->register($builder);

		$builder->register(RouteProvider::class, Routes::class);
		$app = $builder->container()->get(Application::class);

		self::assertInstanceOf(Application::class, $app);
	}
}