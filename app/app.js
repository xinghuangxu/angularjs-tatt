// initializes the module for the app
angular.module("spark", ['ngRoute', 'spark.rally', 'spark.doc', 'spark.boxcar']).
        config(['$routeProvider', function ($routeProvider) {
                $routeProvider.otherwise({redirectTo: 'doc'});
            }]);