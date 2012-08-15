// Backbone setup
Backbone.sync = function(method, model, success, error){ success(); }

_.templateSettings = { interpolate : /\{\{(.+?)\}\}/g };	
	
	
// Models	
	timeapp.models = { 
		mything : Backbone.Model.extend({
			defaults: {
				name: "guest",
				mail: "guest@dispatch.com"
			}
		})
	}
	
	
// Views	

	timeapp.views = {
		lineItem : Backbone.View.extend({
			template : _.template($("#line-item-tmpl").html()),
			initialize : function() { 
				this.render();
			},
			events : {
				"click .log-in" : "goBack",
				"click .log-in" : "nextView"
			},
			render : function() {
//console.log(myData);			
				$("#project-list").append(this.template(myData.attributes));
				return this;
			},
			goBack : function() {
			
			},
			nextView : function() {
			
			}
		})
	}
