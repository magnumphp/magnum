<?php

namespace Magnum\Http\Container;

use Magnum\Http\Message\ServerRequest\Factory;
use Magnum\Http\Stub\Action\CommentedRequestDecorator;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;

class ActionRequestDecoratorTest
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

	public function testHandleDecorates()
	{
		$action = new class {
			public $called = false;
			public $request = null;
			public function handle($request) {
				// just checking that this is called
				$this->called = true;
				$this->request = $request;

				return new Response(200);
			}
		};

		$request = Factory::createFromGlobals($this->globals);
		(new ActionRequestDecorator($action, CommentedRequestDecorator::class))->handle($request);

		self::assertTrue($action->called);
		self::assertInstanceOf(CommentedRequestDecorator::class , $action->request);
	}
}