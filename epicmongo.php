<?php
/*
	Bootstrap Epic_Mongo
	
	This configuration expects that the D3Up/API exists inside of the
	main repository (D3Up/D3Up.com) for the site. It looks for Epic_Mongo 
	one folder above itself, in the bundles folder.
	
	It also uses the models from D3Up/D3Up.com that are located one folder
	up in application/models.
	
	Change these values to point to Epic_Mongo and the Models if it is 
	running individually. 
	
	D3Up.com: http://github.com/D3Up/D3Up.com
	Epic_Mongo: http://github.com/aaroncox/EpicMongo
*/
require_once(__DIR__ . "/../bundles/epic_mongo/Mongo.php");
/* 
	Create a Phalcon\Loader to autoload our Models
*/
$loader = new \Phalcon\Loader();
/*
	Autoload the Models from the main repository
*/
$loader->registerDirs(array(
	__DIR__ . '/../application/models'
))->register();
/*
	Define a default schema, which is limited to specific models.
*/
class Schema extends Epic_Mongo_Schema
{
	/*
		Which DB or Cluster we should use to connect to
	*/ 
	protected $_db = "com_d3up";
	/*
		The typeMap defines quick access to different models
	*/
	protected $_typeMap = array(
		'user' => 'D3Up_user',
		/*
			Class Mappings for Builds
			- 'build' allows access to Epic_Mongo::db('build')
			- 'cursor:build' specifies that the cursor for the
					above build map should be an instance of the
					specified cursor class.
		*/
		'build' => 'D3Up_Build',
		'cursor:build' => 'D3Up_Mongo_Iterator_Cursor',
		/*
			Class Mappings for Items
			- 'item' allows access to Epic_Mongo::db('item')
			- 'cursor:item' specifies that the cursor for the
					above item map should be an instance of the
					specified cursor class.
		*/
		'item' => 'D3Up_Item',
		'cursor:item' => 'D3Up_Mongo_Iterator_Cursor',
	);
}
/*
	Create the Connection for the API to Localhost
*/
Epic_Mongo::addConnection('default', 'localhost');
/*
	Add the our Micro Schema as defined above
*/
Epic_Mongo::addSchema('db', new Schema);