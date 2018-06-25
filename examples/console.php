<?php

$rootPath = dirname(__DIR__);

require_once "{$rootPath}/vendor/autoload.php";

$options = [
	'useConfiguration' => false,
	'useConsole'       => true
];

$container = \Magnum\Container\Adapter\AuraDi::container($rootPath, $options);
$container->get(\Magnum\Console\Application::class)->run();
