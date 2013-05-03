window.app = (function($, Backbone, _) {
    var log = function() {
        console.log.apply(console, arguments);
    }

    var App = {
        views: {},
        models: {},
        collections: {}
    };

    App.models.Method = Backbone.Model.extend({
        defaults: {
            name: "",
            params: []
        }
    });

    App.models.Request = Backbone.Model.extend({
        defaults: {
            "jsonrpc": "2.0",
            "method": "",
            "params": {},
            "id": 1
        }
    });

    App.collections.Methods = Backbone.Collection.extend({
        model: App.models.Method
    });

    App.views.MethodListItemView = Backbone.View.extend({
        template: "#method-list-item-template",
        tagName: "li",

        initialize: function() {
            this.template = _.template($(this.template).html())
            return this;
        },

        render: function() {
            this.$el.html(this.template(this.model.toJSON()));
            return this;
        }
    });

    App.views.MethodsListView = Backbone.View.extend({
        el: "#methods-view",

        initialize: function() {
            this.render();
        },

        render: function() {
            this.$el.empty();

            var that = this;
            this.collection.each(function(model) {
                var view = new App.views.MethodListItemView({
                    model: model
                });

                that.$el.append(view.render().el);
            });
        }
    });

    App.views.RequestView = Backbone.View.extend({
        el: "#request-view",

        initialize: function() {
            this.listenTo(this.model, "change", this.render);
        },

        render: function() {
            this.$el.html(JSON.stringify(this.model.toJSON()));
        }
    });

    App.views.Controls = Backbone.View.extend({
        el: "#controls",
        events: {
            "click #send-request": "sendRequest"
        },

        sendRequest: function() {
            console.log(this.model.toJSON());
        }
    });

    App.views.MethodFormView = Backbone.View.extend({
        template: "#method-form-template",
        el: "#method-form-view",

        events: {
            "keyup input": "updateRequest"
        },

        initialize: function() {
            this.template = _.template($(this.template).html())
            this.listenTo(this.model, "change", this.resetRequest);
            this.listenTo(this.model, "change", this.render);
            return this;
        },

        resetRequest: function() {
            this.options.request.set({
                params: {},
                method: this.model.get("name")
            });
        },

        updateRequest: function(e) {
            var param  = e.currentTarget.id;
            var value  = $(e.currentTarget).val();
            var params = this.options.request.get("params");

            params[param] = value;
            this.options.request.set({ params: params });
            this.options.request.trigger("change");
        },

        render: function() {
            if (!this.model.get("params").length) {
                this.$el.html("Method doesn't require any params");
            } else {
                this.$el.html(this.template(this.model.toJSON()));
            }
            return this;
        }
    });

    App.start = function() {
        var methods = new App.collections.Methods([
            {
                name: "login",
                params: ["email", "password"]
            },

            {
                name: "logout"
            },

            {
                name: "businesses",
                params: ["page", "rpp"]
            }
        ]);

        var currentMethod = new App.models.Method;
        var request = new App.models.Request;

        var methodsView = new App.views.MethodsListView({ collection: methods });
        var methodView  = new App.views.MethodFormView({ model: currentMethod, request: request });
        var requestView = new App.views.RequestView({ model: request });
        var controls     = new App.views.Controls({ model: request });

        var Router = Backbone.Router.extend({
          routes: {
            ":method": "changeMethod"
          },

          changeMethod: function(method) {
              var target = methods.findWhere({ name: method });
              currentMethod.set(target.attributes);
          }
        });

        var router = new Router();
        Backbone.history.start();
    }

    return App;
})(jQuery, Backbone, _);

$(function() {
    window.app.start()
})
