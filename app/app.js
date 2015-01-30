// initializes the module for the app
angular.module("spark", ['ngRoute', 'spark.rally', 'spark.doc', 'spark.boxcarView']).
        config(['$routeProvider', function ($routeProvider) {
                $routeProvider.otherwise({redirectTo: 'doc'});
            }]);