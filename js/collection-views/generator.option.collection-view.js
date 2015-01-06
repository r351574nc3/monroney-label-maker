define(['jquery', 'underscore', 'backbone', 'option-view'], function($, _, Backbone, OptionsListItem) {

    var OptionsList = Backbone.View.extend({
        activated: [],
        
        initialize: function(attrs, opts) {
            //console.log("New Options List", this.collection.userId);
            this.collection = attrs.collection;
            this.input_container = attrs.input_container;
            this.add_item = attrs.add_item;
            this.save_button = attrs.save_button;
            this.input = attrs.input;
            this.price_input = attrs.price_input;
            this.list_items = {};
            this.activated = _.isArray(attrs.activated) ? attrs.activated : [];
            this.render();
        },
        
        fetch_options: function(ids, prices) {
            for (var i = 0; i < ids.length; i++) {
                var model = this.collection.findWhere({id: ids[i]});
                //console.log('fetch ' + this.collection.location + ' options', this.collection.userId);        
                if (model != null) {
                    Backbone.trigger('add_option', model, prices[ids[i]]);
                }
            }
        },
        
        render: function() {
            //console.log("Rendering Options", this.collection.location, this.collection.userId);
            this.stopListening();

            this.listenTo(this.collection, "destroy", this.unrender_list_item);
            this.listenTo(this.collection, "add", this.render_list_item);   
            this.listenTo(Backbone, 'requestReset', this.reset_list_items);
            //this.listenTo(Backbone, 'userLoggedIn', this.remove_list_items); 
            var ucLocation = this.collection.location.charAt(0).toUpperCase() + this.collection.location.substr(1, this.collection.location);
    
            this.listenTo(Backbone, this.collection.location + "OptionsAdded", this.replace_collection);
            //this.listenTo(Backbone, 'requestOptions', this.fetch_options);

            this.listenTo(Backbone, 'modelReloaded', this.update_checked);
    
            $(this.add_item).off('click');
            $(this.add_item).on('click', $.proxy(this.show_input, this));
    
            $(this.save_button).off('click');
            $(this.save_button).on('click', $.proxy(this.add_new_option, this));
            
            this.render_all_items();
            console.log("Updating checked with ", this.activated);
            this.update_checked(this.activated);
        },

        update_checked: function(activated) {
            this.reset_list_items();

            for (var option_idx in activated) {
                var option_id = activated[option_idx];

                var an_option = this.get_option_by_id(option_id);
                if (!_.isNull(an_option)) {
                    an_option.$checkbox.prop("checked", true);
                }
            }
        },

        get_option_by_id: function(id) {
            for (var list_item_idx in this.list_items) {
                if (id == this.list_items[list_item_idx].model.get('id')) {
                    return this.list_items[list_item_idx];
                }
            }
            return null;
        },
        
        reset_list_items: function(user) {
            for (var i in this.list_items) {
                //console.log("Resetting List Items", i);
                this.list_items[i].$checkbox.prop('checked', false);
            }
        },

        render_all_items: function() {
            for (i in this.collection.models) {
                var an_option = this.collection.models[i];
                this.render_list_item(an_option, this.collection, { activated: _.contains(this.activated, an_option.get('i')) ? true : false });
            }
        },
        

        unrender_all_items: function() {
            for (i in this.collection.models) {
                this.unrender_list_item(this.collection.models[i], this.collection);
            }
        },

        unrender_list_item: function(model, collection, options) {
            var item = this.list_items[model.get('id')];
            item.$el.remove();
            delete this.list_items[model.get('id')];
        },
        
        replace_collection: function(collection) {
            //console.log("Replace Collection", collection);
            this.collection.stopListening();
            this.unrender_all_items();

            this.collection = collection;
            this.render();  
        },
        
        render_list_item: function(model, collection, options) {
            //console.log('Rendering an Option', model.get('optionName'));
            this.list_items[model.id] = OptionsListItem.initialize({model: model}); 
            this.list_items[model.id].render(this, options);
        },
        
        show_input: function() { 
            $('#generator-spinner-overlay').fadeIn();
            $('#generator-page-loader').fadeIn();
            var response_msg = 'acceptUserCredentials';
            Backbone.trigger('checkUserCredentials', response_msg);
            this.listenToOnce(Backbone, response_msg, function(response) {
                console.log("Response Message", response);
                $('#generator-spinner-overlay').fadeOut();
                $('#generator-page-loader').fadeOut();
                if (response.message) {
                    $(this.input_container).removeClass('invisible');
                    $(this.add_item).addClass('invisible');
                    $(this.input).focus();
                } else {
                    $('#generator-spinner-overlay').fadeOut();
                    $('#generator-page-loader').fadeOut();
					Backbone.trigger('showFailMessage', 'You must be logged in to perform this action.');
				}
			});
		},
		
		add_new_option: function() {
                          
            $('#generator-spinner-overlay').fadeIn();
            $('#generator-page-loader').fadeIn();
			var new_option_name = $(this.input).val();
			
			if (new_option_name) {
				var new_option_price = $(this.price_input).val(); 
				var location = this.collection.location;
		
				$(this.price_input).val('');
				$(this.input).val('');
				$(this.input_container).addClass('invisible');
				$(this.add_item).removeClass('invisible');
	
				//console.log("New Option For Collection", new_option_name, new_option_price, location);
		
				this.collection.create(
					{
						optionName: new_option_name,
						price: new_option_price,
						location: location,
						activated: true
					}
				);
			} else {
				Backbone.trigger("showFailMessage", "Please Enter an Option Name!");
			}
            $('#generator-spinner-overlay').fadeOut();
            $('#generator-page-loader').fadeOut();
		}
		
	});	

	var initialize = function(attrs, opts) {
		return new OptionsList(attrs, opts);	
	}
	
	return {initialize: initialize};
});
