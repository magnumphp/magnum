<?php

$rootPath = dirname(__DIR__);

require_once "{$rootPath}/vendor/autoload.php";

$options = [
	'useConfiguration' => false,
	'useConsole'       => true
];

$container = \Magnum\Container\Adapter\AuraDi::container($rootPath, $options);
$app = $container->get(\Magnum\Console\Application::class);

$app->beginCommand('mod:test')
	->setDescription("Does a test")
	->end();

$app->run();
