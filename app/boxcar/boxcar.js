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
                                $scope.tree = boxcarContainer.toTreeFormat();
                            }
                    );
                };
                $scope.getTreeData();
                $scope.sort = function (classifier) {
                    $scope.tree = boxcarContainer.toTreeFormat(classifier);
                };
            }]);


//Controller for the popover	
boxcar.controller("boxcarPopoverController", function ($scope, dataService, myAuthentication, $modal, $alert, $sce) {
    //sets the submitted variable to false to reset the error visualizations
    //$scope.submitted = false;

    //Function when the add button is used
    $scope.info = function () {


    };

    $scope.export = function () {


    };

});