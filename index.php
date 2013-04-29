<?php

$loader = new \Phalcon\Loader();

$loader->registerDirs(array(
    __DIR__ . '/models/'
))->register();

$di = new \Phalcon\DI\FactoryDefault();

$di->set('mongo', function() {
     $mongo = new Mongo("mongodb://localhost");
     return $mongo->selectDB('com_d3up');
});

$di->set('collectionManager', function() {
     return new Phalcon\Mvc\Collection\Manager();
});

$app = new Phalcon\Mvc\Micro();

$app->setDI($di);

$app->get('/api/builds', function() {
	var_dump(Builds::findFirst()); 
});

$app->handle();