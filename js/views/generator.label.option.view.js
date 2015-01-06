define(['jquery', 'underscore', 'backbone'], function($, _, Backbone) {
	var LabelOption = Backbone.View.extend({
		tag: 'li',
		class: 'option font-arial px-10',
		
		initialize: function(attrs) {
			this.render();
		},
		
		render: function() {
			var option_name = this.model.get('optionName');
			var displayName = option_name.split(' ').join('').replace(new RegExp(/[^a-zA-Z0-9]/g), '');
			//console.log("Rendering Option " + displayName );
			this.listenTo(this.model, "change:price", this.update_total);			
			$tag = $('<' + this.tag + '>', {class: this.class, id: 'option' + displayName});
			$name = $('<span>', {text: option_name, class: 'basal-font', id: 'option' + displayName + 'Name'});
			this.$value = $('<span>', {class: 'float-right basal-font', text: this.model.get_display_price(), id: 'option' + displayName + 'Value'});
			$tag.append($name, this.$value);
			
			$('#' + this.model.get('location') + 'Options').prepend($tag);	
			this.$el = $('#option' + displayName); //$(this.el); 
			this.el = this.$el[0]; //$('#' + this.model.get('location') + 'Options').children('li')[0];

		},
		detach_from_view: function(label_view) {
			//console.log('Removing El From View', this.$el);
			this.$el.remove();
		},
		
		update_total: function(model) {
			this.$value.text(this.parse_currency(this.model.get('price')));
		},
		
		parse_currency: function(val) {
			val = parseFloat(val).toFixed(2)
			return '+ $' + val.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')			 
		}
		
	});
	
	var initialize = function(attrs, opts) {
		return new LabelOption(attrs, opts);
	};

	return {initialize: initialize};
});