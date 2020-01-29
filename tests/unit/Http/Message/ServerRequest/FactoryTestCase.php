<?php

namespace Magnum\Http\Message\ServerRequest;

use Magnum\Http\Message\ServerRequest;
use Magnum\Http\Stub\HttpFactory;
use Magnum\Http\TestCase;
use Middlewares\Utils\Factory as MiddlewareFactory;
use Middlewares\Utils\FactoryDiscovery;
use Psr\Http\Message\UploadedFileInterface;

class FactoryTestCase
	extends TestCase
{
	protected $streamTest = 'php://input';

	protected $globals = [
		'_SERVER' => [
			'HTTP_HOST'   => 'example.com',
			'SERVER_ADDR' => '1.1.1.1'
		],
		// prevent the factory tests from interfering with the decorators
		'_COOKIE' => [],
		'_FILES'  => []
	];

	public function setUp(): void
	{
		unset($GLOBALS['_FILES']);
		unset($GLOBALS['_COOKIE']);
	}

	public function testResolvesCookies()
	{
		$g = $this->globals;
		unset($g['_COOKIE']);
		$g['_SERVER']['HTTP_COOKIE'] = ['test=kakaw; moo=quack'];

		$request = Factory::createFromGlobals($g);
		self::assertEquals(['test' => 'kakaw', 'moo' => 'quack'], $request->getCookieParams());
	}

	public function testResolvesFiles()
	{

		$g = $this->globals;
		unset($g['_FILES']);
		$GLOBALS['_FILES'] = [
			'kakaw' => [
				'tmp_name' => 'tmp',
				'name'     => 'kakaw.pdf',
				'type'     => 'text/plain',
				'size'     => 200,
				'error'    => 0
			]
		];
		$request           = Factory::createFromGlobals($g);
		$files             = $request->getUploadedFiles();
		self::assertArrayHasKey('kakaw', $files);

		/** @var UploadedFileInterface $file */
		$file = $files['kakaw'];
		self::assertEquals(200, $file->getSize());
		self::assertEquals('kakaw.pdf', $file->getClientFilename());
	}

	public function testBuildsBodyStream()
	{
		$request = Factory::createFromGlobals($this->globals);
		$body    = $request->getBody();
		$md      = $body->getMetadata();
		self::assertInstanceOf($this->bodyClass, $body);
		self::assertEquals($this->streamTest, $md['uri']);
	}
}