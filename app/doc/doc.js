'use strict';

angular.module('spark.doc', ['ngRoute', 'mgcrea.ngStrap'])

        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/doc', {
                    templateUrl: 'doc/README.html',
                    controller: 'DocController'
                });
            }])

        .controller('DocController', ['$scope', function ($scope) {
            }]);