<?php

namespace Magnum\Http\Message\ServerRequest;

use GuzzleHttp\Psr7\CachingStream;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;

class GuzzleFactoryTest
	extends FactoryTestCase
{
	protected $bodyClass  = CachingStream::class;
	protected $streamTest = 'php://temp';

	public function setUp(): void
	{
		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::GUZZLE));
		parent::setUp();
	}
}