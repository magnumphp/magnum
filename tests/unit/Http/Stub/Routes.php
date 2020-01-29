<?php

namespace Magnum\Http\Stub;

use Slim\Turbo\Provider\RouteProvider;

class Routes
	implements RouteProvider
{
	public function register($router)
	{
		$router->get('/', TestAction::class)->setName('home');
	}
}