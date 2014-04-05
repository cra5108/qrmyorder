<?php

// Auto load the composer libraries
require_once __DIR__ . '/vendor/autoload.php';

// TODO: Should be autoloaded by composer autoload
require_once __DIR__ .'/src/qr/controllers/MainController.php';

// Create our routing
$klein = new \Klein\Klein();

// Setup app variables for route callbacks
$klein->respond(function($request, $response, $service, $app) use ($klein) {
	require_once __DIR__ .'/src/qr/config/config.php';

	$app->loader = new Twig_Loader_Filesystem('templates');
	$app->twig = new Twig_Environment($app->loader);
	$app->config = $appConfig;

});

$klein->respond('GET', '/', function ($request, $response, $service, $app) {
	$main = new MainController($app->twig);
	$main->render();
});

$klein->respond('GET', '/hello', function() {
	return 'hello world';
});

$klein->dispatch();