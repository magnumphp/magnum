<?php

namespace Magnum\Http\Message\ServerRequest;

use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;
use Slim\Psr7\Stream;

class SlimFactoryTest
	extends FactoryTestCase
{
	protected $bodyClass = Stream::class;

	public function setUp(): void
	{
		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::SLIM));
		parent::setUp();
	}
}