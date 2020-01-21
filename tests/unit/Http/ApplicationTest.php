<?php

namespace Magnum\Http;

use Magnum\Http\Exception\MissingServerRequest;
use Magnum\Http\Stub\TrackingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\App;
use Slim\Turbo\Provider\RouteProvider;
use Slim\Turbo\Provider\Symfony;
use Slim\Turbo\Routing\Cache\Memory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApplicationTest
	extends TestCase
{
	public function testRunFailsWithoutRequest()
	{
		$this->expectException(MissingServerRequest::class);

		(new Application($this->getMockBuilder(App::class)->disableOriginalConstructor()->getMock()))->run();
	}

	public function testRunReturnsResponse()
	{
		$slim = $this->getMockBuilder(App::class)->disableOriginalConstructor()->getMock();
		$slim->expects(self::once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$app = new Application($slim);
		$response = $app->run($this->buildRequest('GET', '/'));
		self::assertInstanceOf(ResponseInterface::class, $response);
	}

	public function testRunInjectsMiddleware()
	{
		$mockResponse = $this->createMock(ResponseInterface::class);

		// this is crazy
		$container = new ContainerBuilder();
		$container->registerExtension($ext = new Symfony());
		$container->loadFromExtension($ext->getAlias());
		$container->register(RouteProvider::class, Stub\Routes::class);
		$container->register(Stub\TestAction::class)->setPublic(true);
		$container->register(CacheInterface::class, Memory::class)->setPublic(true);

		$container->setAlias(RouteProvider::CACHE_KEY, CacheInterface::class);
		$container->compile();
		$slim = $container->get(App::class);

		$middlewares = [
			new TrackingMiddleware($mockResponse),
			new TrackingMiddleware($mockResponse)
		];

		(new Application($slim, $middlewares))->run($this->buildRequest('GET', '/'));

		foreach ($middlewares as $middleware) {
			self::assertTrue($middleware->seen);
		}
	}

	public function testRunAcceptsConstructorRequest()
	{
		$slim = $this->getMockBuilder(App::class)->disableOriginalConstructor()->getMock();
		$slim->expects(self::once())->method('handle')->willReturn($this->createMock(ResponseInterface::class));

		$app      = new Application($slim, [], $this->buildRequest('GET', '/'));
		$response = $app->run();

		self::assertInstanceOf(ResponseInterface::class, $response);
	}
}