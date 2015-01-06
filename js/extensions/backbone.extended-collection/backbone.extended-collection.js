define(['jquery', 'underscore', 'backbone', 'util/authenticate'], function($, _, Backbone, authenticate) {
	return Backbone.Collection.extend({
		set_user_id: function(user) {
			this.user = user;
		},
		
		set_listeners: function() {
			this.listenTo(Backbone, "userLoggedIn", this.set_user_id);
		},
		
		create: function(attributes, options) {						
 			if (!attributes) return false;
				
			options = options || {};
		
			if (!options['url']) {
				if (typeof this.url === 'function') {
					options['url'] = this.url();
				} else {
					options['url'] = this.url;
				}
			}
			var new_model = new this.model(attributes, options);

 			var new_options = {};
			new_options['data'] = {};
				
			for (i in attributes) {
				new_options.data[i] = new_model.get(i);
				new_options.data = this.camelToSnakeCase([new_options.data])[0];	
			}
			
			new_options.data = JSON.stringify(new_options['data']);			
			new_options.dataType = 'json';
			new_options.processData = false;
			new_options.contentType = 'application/json';
			
			//var success = options['success'] || function(){};
			//var error = options['error'] || function(){};

			new_options.success = $.proxy(function(data, response, xhr) {
				if (typeof data === "string") {
					data = $.parseJSON(data);
				}
	
				if (data.success == true) {
					new_model.set('id', data.id);

					this.add(new_model);
				}
                else if(data.message == "Already Added") {
					console.log("Already Added", json_response);
				}
                else {
					console.log("Unsuccessful", data);
				}
			}, this);

			new_options.error = function(data, response, xhr) {
				console.log("Failure", data, response, xhr);
			};

			for (i in options) {
				if (options[i]) {
					new_options[i] = options[i];
				}
			}		

			new_options.headers = new_options.headers || {};
			new_options.headers['Authentication'] = authenticate(this.user, new_options.url, "POST");					
			return Backbone.sync('create', new_model, new_options);
		},
		
		parse: function(snake, options) {
			var camel = this.snakeToCamelCase(snake);

			var camels = [];
			_.each(camel, function(el, i, li) {
				camels.push(el);
			}, this);

			return camels;
		},
		
		
		camelToSnakeCase: function (camels) {
    		var snakes = []
			for (var i in camels) {
				snakes.push(this._recursiveCamels(camels[i]));
			}
			return snakes;
		},
		
		_recursiveCamels: function(camels, isValue) {
			if (typeof camels == 'string') {
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
			}
            else if (typeof camels == 'object') {
				snake = {};
				for (var key in camels) {
					if (camels[key] != null) {
						snake[this._recursiveCamels(key)] = this._recursiveCamels(camels[key], true);
					}
				}
				return snake;				
			} else if (typeof camels == 'number') {
				return camels;
			}
		},
		
		snakeToCamelCase: function (snakes) {
    		var camels = []
			for (var i in snakes) {
                camel = this._recursiveSnakes(snakes[i]);
				camels.push(camel);
			}
			return camels;
		},
	
		_recursiveSnakes: function(snakes, isValue) {
			if (_.isString(snakes)) {
				isValue = isValue || false;
				
				if (snakes.match(/.*\.[a-zA-Z0-9]{3,4}$/)) {
					return snakes;				
				} else if (isValue == true) {
					return snakes;
				}
				return snakes.toLowerCase().replace(/_(.)/g, function(match, horse) {
					return horse.toUpperCase();
				});
			}
            else if (_.isNumber(snakes)) {
				return snakes;
            }
            else if (_.isArray(snakes)) {
                var arr = [];
                for (var key in snakes) {
                    var arrValue = this._recursiveSnakes(snakes[key], true);
    				arr[key] = arrValue;
                }
                return arr;
            }
            else if (_.isObject(snakes)) {
				var obj = {};
				for (var key in snakes) {
					if (snakes[key] != null) {
                        var objKey   = this._recursiveSnakes(key);
                        var objValue = this._recursiveSnakes(snakes[key], true);
						obj[objKey] = objValue;
					}
				}
				return obj;				
			}
            else {
				//return null;
				//console.log('SnakeCamel:undefined', snakes);
			}
		}
				
	});
});
