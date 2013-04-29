<?php
// Load up our Models in /models
$loader = new \Phalcon\Loader();
$loader->registerDirs(array(
	__DIR__ . '/models/'
))->register();
// Setup our Dependancy Injector
$di = new \Phalcon\DI\FactoryDefault();
// Add a connection to MongoDB and select the DB
$di->set('mongo', function() {
	$mongo = new Mongo("mongodb://localhost");
	return $mongo->selectDB('com_d3up');
});
// Hook in the collectionManager for access to the collections
$di->set('collectionManager', function() {
	return new Phalcon\Mvc\Collection\Manager();
});
// Setup a MicroMVC app
$app = new Phalcon\Mvc\Micro();
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo json_encode(['error' => 'Invalid Request']);
});
// Add in our Dependancy Injector
$app->setDI($di);
require_once("routes.php");
// Handle
$app->handle();