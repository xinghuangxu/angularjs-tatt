// initializes the module for the app
var spark=angular.module("spark", ['ngRoute', 'spark.rallyView', 'spark.doc', 'spark.boxcarView','spark.plannerView']).
        config(['$routeProvider', function ($routeProvider) {
                $routeProvider.otherwise({redirectTo: 'doc'});
            }]);