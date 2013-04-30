var mongoose = require('mongoose'),
		Schema = mongoose.Schema,
		ObjectId = Schema.ObjectId;
 
var buildSchema = new Schema({name: String}, { collection : 'builds' });

buildSchema.methods.toJSON = function() {
	var fields = {
		'id'						: null,
		'name'					: null,
		'class'					: 'heroClass',
		'level'					: null,
		'hardcore'			: null,
		'paragon'				: null,
		'actives'				: null,
		'passives'			: null,
		'_characterId'	: 'bt-id',
		'_characterBt'	: 'bt-tag',
		'_characterRg'	: 'bt-srv'
	};
	var data = {};
  obj = this.toObject()
	if(obj) {
		Object.keys(fields).forEach(function(k,v) {
			if(obj[k]) {
				data[k] = obj[k];
			}
		});
		if(obj['stats']) {
			if(obj['stats']['dps']) {
				data['dps'] = obj['stats']['dps'];				
			}
			if(obj['stats']['dps']) {
				data['ehp'] = obj['stats']['ehp'];
			}
		}
	  return data;		
	}
	return obj;
}

module.exports = mongoose.model('Builds', buildSchema);