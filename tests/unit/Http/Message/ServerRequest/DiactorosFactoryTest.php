<?php

namespace Magnum\Http\Message\ServerRequest;

use Laminas\Diactoros\PhpInputStream;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;

class DiactorosFactoryTest
	extends FactoryTestCase
{
	protected $bodyClass = PhpInputStream::class;

	public function setUp(): void
	{
		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::DIACTOROS));
		parent::setUp();
	}
}