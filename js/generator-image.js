
	var Img = Backbone.Model.extend({
		defaults: {
			guid: '',
			caption: '',
		},
	
		initialize: function(attrs, opts) {
			//console.log("New Img", attrs, opts);	
		},
	
		handle_server_sync: function(model, response, options) {
			//debug_server_response("Handling Server Sync", model, response, options);		
		},
		
		handle_server_error: function(model, response, options) {
			//debug_server_response("Handling Server Error", model, response, options);		
		},
	
		urlRoot: restful.url + 'label_images/'
	});
	
	var Imgs = Backbone.ExtendedCollection.extend({
		model: Img,
		url: restful.url + 'label_images/',
		initialize: function(models, opts) {
			
		},
	});
	
	var ImgView = Backbone.View.extend({
		className: 'customLabelView',
		tagName: 'img',
		
		initialize: function() {
			//this.model.on('change:dealershipLogo', this.render, this);	
			console.log('ImgView', this);
			this.render();
		},
		
		render: function(collection) {
			this.$el.attr('src', this.model.get('guid'));
			
			this.$el.click($.proxy(this.select_image, this));
			
			return this;
		},
		
		select_image: function() {
			Backbone.trigger('select_featured_image', this.model);
		}
	
	});
	
	var ImgsView = Backbone.View.extend({
		el: '.tag-gallery',
		
		
		initialize: function(attrs, opts) {
			//console.log('Initialize ImgsView', this.collection);
			this._imgViews = {};
			//this.listenTo(this.collection, 'change:selected', this.render_image);				
			this.collection.on("add", this.handle_add, this);		
			Backbone.on('customLabelAdded', this.add_new_image, this);
			Backbone.on('requestCustomLabel', this.handle_request, this); 

			this.render();
		},
		
		handle_request: function(id) {
			var img = this.collection.get(id);
			console.log('Handle Request', id, img);
			if (img) Backbone.trigger('returnCustomLabel', img); 
		},
		
		add_new_image: function(guid, caption) {
			this.collection.create({guid: guid, caption: caption});
		},
		
		handle_add: function(model, collection, options) {
			
			this._imgViews[model.cid] = new ImgView({model: model});
			this._imgViews[model.cid].render(this);
			
			this.$el.prepend(this._imgViews[model.cid].el);
		},
			
		render: function() {
			var images = this;

			_.each(this.collection.models, function(el, i, li) {
				this._imgViews[el.id] = new ImgView({model: el});				
				$(this.el).prepend(this._imgViews[el.id].el);			
			}, this);

		},
		
		get_view_by_cid: function(cid) {
			return _imgViews[cid];		
		},
		

	});