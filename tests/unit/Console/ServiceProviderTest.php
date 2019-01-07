<?php

namespace Magnum\Console;

use Magnum\Console\Fixture\TestProvider;
use Magnum\Container\Builder;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest
	extends TestCase
{
	/**
	 * @var Builder
	 */
	protected $builder;

	public function setUp()
	{
		$this->builder = new Builder();

		(new ServiceProvider())->register($this->builder);

		// this is necessary so we can actually call get() on the container
		$this->builder->get(Application::class)->setPublic(true);

		$this->builder->setParameterDefault('path', '.');
	}

	public function testRegistration()
	{
		$app = $this->builder->container()->get(Application::class);
		self::assertInstanceOf(Application::class, $app);
	}

	public function testRegisterUsesCustomVersion()
	{
		$this->builder->setParameter(ServiceProvider::APP_VERSION_KEY, '1.0.2');

		/** @var Application $app */
		$app = $this->builder->container()->get(Application::class);

		self::assertEquals('1.0.2', $app->getVersion());
	}

	public function testRegisterUsesStaticVersion()
	{
		$b = new Builder();
		$b->setParameterDefault('path', '.');

		(new TestProvider())->register($b);

		$b->get(Application::class)->setPublic(true);

		$app = $b->container()->get(Application::class);
		self::assertEquals(TestProvider::DEFAULT_APP_VERSION, $app->getVersion());
	}
}