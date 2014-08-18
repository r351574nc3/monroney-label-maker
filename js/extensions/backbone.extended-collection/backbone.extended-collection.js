define(['jquery', 'underscore', 'backbone'], function($, _, Backbone) {
	return Backbone.Collection.extend({
		set_user_id: function(user) {
			this.userId = user.get('id');
			this.userName = user.get('name');
			console.log("Current User", this.userName + "(" + this.userId + ")");
		},
		
		set_listeners: function() {
			this.listenTo(Backbone, "userLoggedIn", this.set_user_id);
		},
		
		create: function(attributes, options) {						
 			if (!attributes) return false;
			
			options = options || {};
			
			console.log("Collection URL", this.url);
			
			var lastIndex = this.url.lastIndexOf('/');
			var urlFront = this.url.substr(0, lastIndex);
			var urlBack = this.url.substr(lastIndex + 1, this.url.length);			
			
			options['url'] = urlFront + this.userName + "/" + this.urlBack;
							
			var new_options = {};
			attributes['userId'] = this.userId; 
			
			var new_model = new this.model(attributes, options);
			
			//console.log("New Model", new_model, options);
 				
			new_options['data'] = {};
				
			for (i in attributes) {
				new_options['data'][i] = new_model.get(i);
				new_options['data'] = this.camelToSnakeCase([new_options['data']])[0];	
			}
			
			new_options['dataType'] = 'json';
			new_options['processData'] = true;
			
			//var success = options['success'] || function(){};
			//var error = options['error'] || function(){};

			new_options['success'] = $.proxy(function(data, response, xhr) {
				if (typeof data == "String") {
					data = $.parseJSON(data);
				}
	
				if (data.success == true) {
					//console.log('Success', data);
					this.add(new_model);
					//console.log(this.collection);
				} else if(data.message == "Already Added") {
					//console.log("Already Added", json_response);
				}
				//success(data, response, xhr);
			}, this);

			new_options['error'] = function(data, response, xhr) {
				error(data, response, xhr);
				console.log("Failure", data, response, xhr);
			};

			for (i in options) {
				new_options[i] = options[i];
			}		

			
			return Backbone.sync('create', new_model, new_options);
		},
		
		parse: function(snake, options) {
			var camel = this.snakeToCamelCase(snake);
			
			var camels = [];
			_.each(camel, function(el, i, li) {
				camels.push(el);
			}, this);
			//console.log('Parse', snake, camel, camels, options);
			return camels;
		},
		
		
		camelToSnakeCase: function (camels) {
    		var snakes = []
			//console.log('SNAKES', snakes);
			for (var i in camels) {
				snakes.push(this._recursiveCamels(camels[i]));
			}
			console.log('SnakeCamel', snakes);
			return snakes;
		},
		
		_recursiveCamels: function(camels, isValue) {
			if (typeof camels == 'string') {
				//console.log('SnakeCamel:string', camels, isValue);
				isValue = isValue || false;
				//Check if function is returning a url or file
				if (camels.match(/.*\.[a-zA-Z0-9]{3,4}$/)) {
					return camels;				
				} else if (isValue == true) {
					return camels;
				}
				
				return camels.replace(/([A-Z])/, function(match, horse) {
					return '_' + horse.toLowerCase();
				});
			} else if (typeof camels == 'object') {
				snake = {};
				for (var key in camels) {
					if (camels[key] != null) {
						//console.log('SnakeCamel:object', key, camels[key]);
						snake[this._recursiveCamels(key)] = this._recursiveCamels(camels[key], true);
					}
				}
				return snake;				
			} else if (typeof camels == 'number') {
				//console.log('SnakeCamel:number', camels);

				return camels;
			} else {
				//return null;
				//console.log('SnakeCamel:undefined', camels);
			}
		},
		
		snakeToCamelCase: function (snakes) {
    		var camels = []
			//console.log('SNAKES', snakes);
			for (var i in snakes) {
				camels.push(this._recursiveSnakes(snakes[i]));
			}
			return camels;
		},
		
		_recursiveSnakes: function(snakes, isValue) {
			if (typeof snakes == 'string') {
				isValue = isValue || false;
				//console.log('SnakeCamel:string', snakes);
				
				if (snakes.match(/.*\.[a-zA-Z0-9]{3,4}$/)) {
					return snakes;				
				} else if (isValue == true) {
					return snakes;
				}
				
				return snakes.toLowerCase().replace(/_(.)/g, function(match, horse) {
					return horse.toUpperCase();
				});
			} else if (typeof snakes == 'object') {
				camel = {};
				for (var key in snakes) {
					if (snakes[key] != null) {
						//console.log('SnakeCamel:object', key, snakes[key]);
						camel[this._recursiveSnakes(key)] = this._recursiveSnakes(snakes[key], true);
					}
				}
				return camel;				
			} else if (typeof snakes == 'number') {
				//console.log('SnakeCamel:number', snakes);

				return snakes;
			} else {
				//return null;
				//console.log('SnakeCamel:undefined', snakes);
			}
		}
				
	});
});