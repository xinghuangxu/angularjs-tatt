// initializes the module for the app
var spark=angular.module("spark", ['ngRoute', 'spark.rallyView', 'spark.doc', 'spark.boxcarView']).
        config(['$routeProvider', function ($routeProvider) {
                $routeProvider.otherwise({redirectTo: 'doc'});
            }]);