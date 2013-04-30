var express = require('express');
var mongoose = require('mongoose');
var app = express();
 
mongoose.connect('mongodb://localhost/com_d3up');
 
app.configure(function(){
  app.use(express.bodyParser());
  app.use(express.methodOverride());
  app.use(app.router);
});
 
var api = require('./api.js');
app.get('/api/builds', api.get);
 
app.listen(3000);