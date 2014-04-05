<?php

// Auto load the composer libraries
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ .'/src/qr/config/config.php';
// TODO: Should be autoloaded by composer autoload
require_once __DIR__ .'/src/qr/models/MenuItems.php';
require_once __DIR__ .'/src/qr/controllers/MainController.php';

// Create our routing
$klein = new \Klein\Klein();

// Setup app variables for route callbacks
$klein->respond(function($request, $response, $service, $app) use ($klein, $appConfig) {

	$app->register('db', function() {
		$db = new PDO('mysql:host=54.85.166.107;dbname=myqrorder;charset=utf8', 'dylan', '3$9ssly&');
		return $db;
	});

	$app->register('MenuItemsModel', function() use ($app) {
		return new MenuItems($app->db);
	});

	$app->register('MenuController', function() use ($app)  {
		return new MainController($app);
	});

	$app->loader = new Twig_Loader_Filesystem('templates');
	$app->twig = new Twig_Environment($app->loader);
	$app->config = $appConfig;

});

$klein->respond('GET', '/', function ($request, $response, $service, $app) {
	$app->MenuController->index();
});

$klein->respond('GET', '/createorder', function ($request, $response, $service, $app) {
	$app->MenuController->createOrder();
});

$klein->dispatch();