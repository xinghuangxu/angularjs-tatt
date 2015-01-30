'use strict';

angular.module('spark.boxcarView', ['spark.boxcar'])
        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/boxcar', {
                    templateUrl: 'vBoxcar/boxcarView.html',
                    controller: 'BoxcarViewController'
                });
            }])
        .controller('BoxcarViewController', ['$scope', 'boxcarDataService', '$log', 'boxcarContainer', '$templateCache', '$compile', function ($scope, boxcarDataService, $log, boxcarContainer, $templateCache, $compile) {
                
            }]);