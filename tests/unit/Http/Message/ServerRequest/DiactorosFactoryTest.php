<?php

namespace Magnum\Http\Message\ServerRequest;

use Laminas\Diactoros\Stream;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;

class DiactorosFactoryTest
	extends FactoryTestCase
{
	protected $bodyClass = Stream::class;

	public function setUp(): void
	{
		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::DIACTOROS));
		parent::setUp();
	}
}