<?php

namespace Magnum\Http\Message\ServerRequest;

use Magnum\Http\Message\ServerRequest;
use Magnum\Http\Stub\HttpFactory;
use Magnum\Http\TestCase;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;

class FactoryTest
	extends TestCase
{
	protected $globals = [
		'_SERVER' => [
			'HTTP_HOST'   => 'example.com',
			'SERVER_ADDR' => '1.1.1.1'
		],
		// prevent the factory tests from interfering with the decorators
		'_COOKIE' => [],
		'_FILES'  => []
	];

	public static function setUpBeforeClass(): void
	{
		$GLOBALS['_FILES'] = $GLOBALS['_COOKIE'] = [];
	}

	protected function tearDown(): void
	{
		// reset to defaults
		MiddlewareFactory::setFactory(new FactoryDiscovery);
	}

	public function testCreateFromGlobalsReturnsDiactorosAsDefault()
	{
		/** @var ServerRequest $request */
		$request = Factory::createFromGlobals($this->globals);
		self::assertInstanceOf(ServerRequest::class, $request);
	}

	public function testCreateFromGlobalsReturnsGuzzle()
	{
		if (!class_exists(\GuzzleHttp\Psr7\HttpFactory::class)) {
			self::markTestSkipped("Please use guzzlehttp/psr7@dev-master until the next release.");
		}

		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::GUZZLE));

		self::assertInstanceOf(ServerRequest::class, Factory::createFromGlobals($this->globals));
	}

	public function testCreateFromGlobalsReturnsSlim()
	{
		MiddlewareFactory::setFactory(new FactoryDiscovery(FactoryDiscovery::SLIM));

		self::assertInstanceOf(ServerRequest::class, Factory::createFromGlobals($this->globals));
	}

	public function testCreateFromGlobalsThrowsException()
	{
		$this->expectException(\RuntimeException::class);
		MiddlewareFactory::setFactory(new FactoryDiscovery(HttpFactory::class));

		Factory::createFromGlobals($this->globals);
	}

	public function testCreateFromGlobalsUpdatesHeaders()
	{
		$globals            = $this->globals;
		$globals['_SERVER'] += [
			'HTTP_KAKAW'     => null,
			'CONTENT_LENGTH' => 100,
		];

		$r = Factory::createFromGlobals($globals);
		self::assertEquals(['host' => ['example.com'], 'content-length' => [100]], $r->getHeaders());
	}

	public function testCreateFromGlobalsUsesGlobalsIfNotPassedIn()
	{
		$GLOBALS['_SERVER']                += $this->globals['_SERVER'];
		$GLOBALS['_SERVER']['REQUEST_URI'] = '/';
		$GLOBALS['_COOKIE']                = [];

		$r = Factory::createFromGlobals();

		self::assertEquals('example.com', $r->getUri()->getAuthority());
	}

	public function testParsedBodyIsFilledIn()
	{
		$globals            = $this->globals;
		$globals['_POST']   = ['kakaw' => true];
		$globals['_SERVER'] += [
			'HTTP_KAKAW'     => null,
			'CONTENT_LENGTH' => 100,
		];

		$r = Factory::createFromGlobals($globals);
		self::assertEquals($globals['_POST'], $r->getParsedBody());
	}
}