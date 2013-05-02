<?php
/* 
	Setup a MicroMVC app
*/
$app = new Phalcon\Mvc\Micro();

require_once("jsonp.php");

/*
	Setup the 404 Error Response for all non-found routes
*/
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo json_encode(array('error' => 'Invalid Request'));
});
/*
	Set all response types to application/json
*/
header('Content-type: application/json');
/*
	Bootstrap EpicMongo
*/
require_once("epicmongo.php");
/*
	Include our routes to define the API 
*/
require_once("routes.php");
/*
	Handle any requests
*/
$app->handle();