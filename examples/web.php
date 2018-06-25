<?php
$rootPath = dirname(__DIR__);

require_once "{$rootPath}/vendor/autoload.php";

$options = [
	'useConfiguration' => false,
	'useHttp'       => true
];

$container = \Magnum\Container\Adapter\League::container($rootPath, $options);

$app = $container->get(\Magnum\Http\Application::class);

$container->add(
	'kakaw', new \Magnum\Http\Action\Lambda(
	function ($req) {
		return new \Slim\Http\Response(201);
	}));

$router = $container->get(\Magnum\Http\Routing\Router::class);
$router->get('/', 'kakaw');

$request = \Slim\Http\Request::createFromGlobals(
	\Slim\Http\Environment::mock(
		[
			'REQUEST_URI'    => '/',
			'REQUEST_METHOD' => 'GET'
		])
);

// you have the response, but the default middleware sends it
$response = $app->run($request);

print_r($response);
