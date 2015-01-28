'use strict';

var boxcar = angular.module('spark.boxcar', ['ngAnimate', 'ngSanitize', 'mgcrea.ngStrap', 'ngResource', 'colorpicker.module', 'wysiwyg.module', 'ngRoute'])
        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/boxcar', {
                    templateUrl: 'boxcar/boxcar.html',
                    controller: 'BoxcarController'
                });
            }])
        .controller('BoxcarController', ['$scope', 'boxcarDataService', '$log', 'boxcarContainer', function ($scope, boxcarDataService, $log, boxcarContainer) {
                $scope.boxcarDataService = boxcarDataService;
                //Function to request tree data
                $scope.getTreeData = function () {

                    //Service call to request tree data
                    boxcarDataService.resource.children(
                            {boxcarid: $scope.boxcarid, },
                            {},
                            function (val, response)
                            {

                                $log.log("Tree Data: ", val);
                                boxcarContainer.create(val);
                                $scope.tree = boxcarContainer.toTreeFormat('pr');
                            }
                    );
                };
                $scope.getTreeData();
                $scope.classifiers = [
                    {"value": 'pr', "text": "PR"},
                    {"value": 'qual', "text": "Qualification Area"},
                    {"value": 'impact', "text": "Impact Area"},
                    {"value": 'approach', "text": "Approach"},
                    {"value": 'ownership', "text": "Ownership"}
                ];
                $scope.$watch('selectedClassifier', function (newValue, oldValue) {
                    $scope.tree = boxcarContainer.toTreeFormat(newValue.value);
                });
                
            }]);


//Controller for the popover	
boxcar.controller("boxcarPopoverController", function ($scope) {
    //sets the submitted variable to false to reset the error visualizations
    //$scope.submitted = false;

    //Function when the add button is used
    $scope.info = function () {


    };

    $scope.export = function () {


    };

});