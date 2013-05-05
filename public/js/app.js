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
            test: false,
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

    App.models.Response = Backbone.Model.extend();

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
        testMethodsTitle: "<li>For testing purposes only</li>",
        methodsTitle: "<li>Main server methods</li>",

        initialize: function() {
            this.testMethods = $("#test-methods-view");
            this.render();
        },

        render: function() {
            this.$el.html(this.methodsTitle);
            this.testMethods.html(this.testMethodsTitle);

            var that = this;
            this.collection.each(function(model) {
                var view = new App.views.MethodListItemView({
                    model: model
                });

                if (model.get("test")) {
                    that.testMethods.append(view.render().el);
                } else {
                    that.$el.append(view.render().el);
                }
            });
        }
    });

    App.views.JsonView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, "change", this.render);
        },

        render: function() {
            var json = JSON.stringify(this.model.toJSON(), null, "  ");
            this.$el.html(json);
            hljs.highlightBlock(this.el);
        }
    });

    App.views.Controls = Backbone.View.extend({
        el: "#controls",
        events: {
            "click #send-request": "sendRequest"
        },

        initialize: function() {
            this.listenTo(this.model, "send", this.sendRequest);
        },

        sendRequest: function() {
            var request = JSON.stringify(this.model.toJSON());
            var url     = this.$el.find("#server-url").val();
            var that    = this;

            $.ajax(url, {
                type: "POST",
                data: request,
                processData: false,
                contentType: "application/json"
            }).done(function(data) {
                that.options.response.clear();
                that.options.response.set(data);
            });
        }
    });

    App.views.MethodFormView = Backbone.View.extend({
        template: "#method-form-template",
        el: "#method-form-view",

        events: {
            "keyup input": "updateRequest"
        },

        keyCodes: {
            ENTER: 13,
            BACKSPACE: 8
        },

        prev: {},

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

            switch (e.keyCode) {
                case this.keyCodes.ENTER:
                    this.options.request.trigger("send");
                    return;

                case this.keyCodes.BACKSPACE:
                    if (_.isEmpty(this.prev[param])) {
                        delete params[param];
                        break;
                    }

                default:
                    params[param] = value;
            }

            this.prev = params;
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
                params: ["page", "rpp", "include_reviews"]
            },

            {
                name: "products",
                params: ["business_id", "page", "rpp", "include_bookings"]
            },

            {
                name: "book",
                params: ["booking_id", "start_time"]
            },

            {
                name: "productStatus",
                params: ["product_id"]
            },

            {
                name: "isProductAvailable",
                params: ["product_id", "booking_id", "start_time"]
            },

            {
                name: "pendingBookings"
            },

            {
                name: "approveBooking",
                params: ["product_booking_id"]
            },

            {
                name: "rejectBooking",
                params: ["product_booking_id"]
            },

            {
                name: "addReview",
                params: ["business_id", "title", "body"]
            },

            {
                name: "createUser",
                test: true,
                params: ["email", "password", "first_name", "last_name", "phone_number", "country", "city", "address"]
            },

            {
                name: "createBusiness",
                test: true,
                params: ["user_id", "name", "description", "phone_number", "country", "city", "address"]
            },

            {
                name: "createProduct",
                test: true,
                params: ["business_id", "name", "description", "price", "photo"]
            },

            {
                name: "createBooking",
                test: true,
                params: ["product_id", "duration", "price"]
            },

            {
                name: "createProductBooking",
                test: true,
                params: ["booking_id", "user_id", "start_time", "status"]
            },

            {
                name: "createReview",
                test: true,
                params: ["business_id", "user_id", "title", "body"]
            }

        ]);

        var currentMethod = new App.models.Method;
        var request       = new App.models.Request;
        var response      = new App.models.Response;

        var methodsView  = new App.views.MethodsListView({ collection: methods });
        var methodView   = new App.views.MethodFormView({ model: currentMethod, request: request });
        var requestView  = new App.views.JsonView({ el: "#request-view pre code", model: request });
        var responseView = new App.views.JsonView({ el: "#response-view pre code", model: response });
        var controls     = new App.views.Controls({ model: request, response: response });

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
