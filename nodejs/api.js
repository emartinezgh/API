var builds = require('./build.js');
  
exports.get = function(req, res) {
	/*
		Default Query Params
		
		The $conditions variable is the query that is being passed to MongoDB

		Default Values:
			public = true (Only show public builds)
	*/
	var conditions = {
		'public': true,			
	};
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
	var heroClass = req.query['class'];
	if(heroClass) {
		conditions['class'] = heroClass;
	}
	/*
		$_GET['actives'] Param
		
		Specify skills to filter builds by using a "|" delimited list. All specified actives
			skills must be on the profile to cause a match.
		
		Usage: 
			/api/builds?actives=blizzard~c
			/api/builds?actives=blizzard~c|meteor~e
	*/
	var actives = req.query['actives'];
	if(actives) {
		actives = actives.split("|");
		conditions['actives'] = {'$all': actives};
	}
	/*
		Default Sorting Order
		
		The order in which builds will be sorted, by default the sort is an empty array
	*/
	var sort = {};
	/*
		$_GET['sort'] Param 
		
		Specify a sort order for the query on predefined values. We don't want to allow sorting 
			on everything, otherwise we'd need indexes galore.
		
		Usage: 
			/api/builds?sort=dps
			/api/builds?sort=ehp
	*/
	var sortBy = req.query['sort'];
	if(sortBy) {
		switch(sortBy) {
			case "dps":
				sort['stats.dps'] = -1;
				break;
			case "ehp":
				sort['stats.ehp'] = -1;
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
	var limit = req.query['limit'];
	if(!limit) {
		limit = 100;
	}
	/*
		$_GET['page'] Param
		
		Specify a Page to paginate through the results
		
		Usage: 
			/api/builds?page=10
			/api/builds?page=50
	*/
	var page = req.query['page'];
	if(!page) {
		page = 1;
	}
	/*
		Determine the Skip Value based on the Limit and Page params
	*/
	var skip = limit * (page - 1);
	/*
		Limitations of Input
		
		Currently:
			Limit has to be <= 100
			Skip has to be <= 10000
	*/
	if(limit > 100) {
		// echo json_encode(['error' => 'The maximum results per request is 100.']);
		// exit;
	}
	if(skip >= 10000) {
		// echo json_encode(['error' => 'The depth of pagination is 100, limiting you to 10,000 results maximum. Please refine your query if you are seeking something specific.']);		
		// exit;
	}
	/*
		Execute the Query and return JSON
	*/
	builds
	.find(conditions)
	.limit(limit)
	.skip(skip)
	.sort(sort)
	.exec(function(err, threads) {
    res.send(threads);
  });
}