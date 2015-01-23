// initializes the module for the app
var rally = angular.module("spark.rally", ['ngAnimate', 'ngSanitize', 'mgcrea.ngStrap', 'ngResource', 'colorpicker.module', 'wysiwyg.module', 'ngRoute'])
        .config(['$routeProvider',
            function ($routeProvider) {
                $routeProvider.
                        when('/rally/login', {
                            templateUrl: 'rally/login.html',
                            controller: 'myLogin'
                        }).
                        when('/rally', {
                            templateUrl: 'rally/main.html',
                            controller:"main",
                            resolve: {
                                auth: ["myAuthentication", "$location",function (myAuthentication,$location) {
                                        if(myAuthentication.loginView){
                                            $location.path("/rally/login");
                                        }
                                    }]
                            }
                        });
            }]);	 