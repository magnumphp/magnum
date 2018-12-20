<?php
namespace Magnum\Http\Routing\Container;

use Magnum\Http\Routing\RouteCollector;
use Magnum\Http\Routing\RouteProvider;

class StubProvider
	implements RouteProvider
{
	public function register(RouteCollector $collector)
	{
		$collector->get('/', 'home', 'home');
	}
}