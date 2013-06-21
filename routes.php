<?php
/*
	
	Builds API Route (http://api.d3up.com/builds)
	
	This route is used for retrieving multiple builds based on
	query parameters passed in.
	
	All query parameters are passed in via GET (?var1=1&var2=2 etc)
	
*/
$app->get('/builds', function() use($app) {
	/*
		Default Query
		
		The $query variable is the query that is being passed to MongoDB

		Default Values:
			public = true (Only show public builds)
	*/
	$query = array(
		// 'public' => true, // TODO - Uncomment this once this flag is available on the live DB.
	);
	/*
		Is this query specific to a battletag?
	*/
	if($battletag = $app->request->get('battletag')) {
		/*
			The Browser application passes battletags in with a dash instead of a hash.
		*/
		$battletag = str_replace("-", "#", $battletag);
		/*
			Add it to the Query now with it's proper hash
		*/
		$query['_characterBt'] = $battletag;
	}
	/*
		Is this query specific to a user?
	*/
	if($user_id = $app->request->get('user')) {
		/*
			Load the User
		*/ 
		$user = Epic_Mongo::db('user')->findOne(array("id" => (int) $user_id));
		/*
			Throw an error if we can't find the User
		*/
		if(!$user) {
			echo jsonp_encode(array('error' => 'Invalid User'), $app); exit;
		}
		/*
			Append this user to the Query
		*/
		$query['_createdBy'] = $user->createReference();
	}
	/*
		$_GET['class'] Param
		
		Specifies a character class to return only specific types
		
		Usage:
			/api/builds?class=barbarian
			/api/builds?class=wizard
			/api/builds?class=demon-hunter
			/api/builds?class=witch-doctor
			/api/builds?class=monk
	*/
	if($class = $app->request->get('class')) {
		if(in_array($class, array('barbarian', 'monk', 'witch-doctor', 'wizard', 'demon-hunter'))) {
			$query['class'] = $class;			
		}
	}
	/*
		$_GET['actives'] Param
		
		Specify skills to filter builds by using a "|" delimited list. All specified actives
			skills must be on the profile to cause a match.
		
		Usage: 
			/api/builds?actives=blizzard~c
			/api/builds?actives=blizzard~c|meteor~e
	*/
	if($actives = $app->request->get('actives')) {
		/*
			If we didn't recieve an array from the request, explode by | to force an array
		*/
		if(!is_array($actives)) {
			$actives = explode("|", $actives);
		}
		$query['actives'] = array('$all' => $actives);
	}
	/*
		Default Sorting Order
		
		The order in which builds will be sorted, by default the sort is an empty array
	*/
	$sort = array(
		'_lastCrawl' => -1
	);
	/*
		$_GET['sort'] Param 
		
		Specify a sort order for the query on predefined values. We don't want to allow sorting 
			on everything, otherwise we'd need indexes galore.
		
		Usage: 
			/api/builds?sort=dps
			/api/builds?sort=ehp
	*/
	if($sortBy = $app->request->get('sort')) {
		switch($sortBy) {
			case "dps":
				$sort['stats.dps'] = -1;
				break;
			case "ehp":
				$sort['stats.ehp'] = -1;
				break;
		}
	}
	/*
		$_GET['limit'] Param 
	
		Specify a Limit for the number of returned builds, default is 100.
		
		Usage: 
			/api/builds?limit=1
			/api/builds?limit=50
	*/
	$limit = $app->request->get('limit', 'int', 50);
	/*
		$_GET['page'] Param
		
		Specify a Page to paginate through the results
		
		Usage: 
			/api/builds?page=10
			/api/builds?page=50
	*/
	$page = $app->request->get('page', 'int', 1);
	/*
		Determine the Skip Value based on the Limit and Page
	*/
	$skip = $limit * ($page - 1);
	/*
		Limitations of Input
		
		Currently:
			Limit has to be <= 50
			Skip has to be <= 10000
	*/
	if($limit > 50) {
		echo jsonp_encode(['error' => 'The maximum results per request is 50.'], $app);
		exit;
	}
	if($skip >= 10000) {
		echo jsonp_encode(['error' => 'The depth of pagination is 100, limiting you to 10,000 results maximum. Please refine your query if you are seeking something specific.'], $app);		
		exit;
	}
	/*
		Execute the Query
	*/
	$data = Epic_Mongo::db('build')->find($query)->sort($sort)->limit($limit)->skip($skip);
	/*
		Are we asking for Battle.net Builds?
	*/
	if($app->request->get('battlenet')) {
		/*
			Did we query a battle tag?
		*/
		if(isset($query['_characterBt'])) {
			/*
				Attempting to scan Battle.net for the Battle Tag
			*/
			require_once("../application/libraries/D3Up/Sync.php");
			$sync = new D3Up_Sync();
			$data = array();
			foreach(array(1 => 'US', 2 => 'EU', 3 => 'AS') as $key => $region) {
				$data += $sync->apiGetCharacters($key, $query['_characterBt']);
			} 
			echo jsonp_encode($data, $app); exit;
			
		}
		/*
			Throw an error if we can't find any matches
		*/
		echo jsonp_encode(array('error' => 'Invalid Request'), $app); exit;
	}
	/*
		If the request has the explain parameter, dump out the info about the query and exit
	*/
	if($app->request->get('explain')) {
		echo jsonp_encode(array(
			'info' => $data->getInnerIterator()->info(),
			'explain' => $data->getInnerIterator()->explain()
		), $app);
		exit;
	}
	
	/*
		Render the data as JSON 
	*/
	echo jsonp_encode($data->json(), $app);
});
/*
	
	Build API Route (http://api.d3up.com/build/{id})
	
	This route is used for retrieving a single build by ID.
	
	The ID parameter is the 2nd part of the URL and is required.
	
*/
$app->get('/builds/{id}', function($id) use($app) {
	/*
		Execute the Query
	*/
	$data = Epic_Mongo::db('build')->findOne(array("id" => (int) $id));
	/*
		Throw an error if we can't find the Build
	*/
	if(!$data) {
		echo jsonp_encode(array('error' => 'Invalid Request'), $app); exit;
	}
	/*
		Render the data as JSON 
	*/
	echo jsonp_encode($data->json(), $app);
});

/*
	
	Build API Route (http://api.d3up.com/build/{id}.js)
	
	This route is used for retrieving a single build by ID and injecting it into the D3Up Javascript object.
	
	The ID parameter is the 2nd part of the URL and is required.
	
*/
$app->get('/builds/{id}.js', function($id) use($app) {
	/*
		Execute the Query
	*/
	$data = Epic_Mongo::db('build')->findOne(array("id" => (int) $id));
	/*
		Throw an error if we can't find the Build
	*/
	if(!$data) {
		exit;
	}
	header('Content-type: text/javascript');
	/*
		Render a Javascript File
	*/
echo "(function() {
	d3up.addBuild(".$data->id.", ".json_encode($data->json(true)).");
})();";
});

/*
	
	Build API Route (http://api.d3up.com/build/{id}.js)
	
	This route is used for retrieving a single build by ID and injecting it into the D3Up Javascript object.
	
	The ID parameter is the 2nd part of the URL and is required.
	
*/
$app->get('/builds/{id}.json', function($id) use($app) {
	/*
		Execute the Query
	*/
	$data = Epic_Mongo::db('build')->findOne(array("id" => (int) $id));
	/*
		Throw an error if we can't find the Build
	*/
	if(!$data) {
		exit;
	}
	header('Content-type: text/javascript');
	// header('Last-Modified: '.gmdate('D, d M Y H:i:s', $data->_lastUpdated).' GMT', true, 304);
	/*
		Render a Javascript File
	*/
	echo jsonp_encode($data->json(true), $app);
});