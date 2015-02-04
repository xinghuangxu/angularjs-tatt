'use strict';

angular.module('spark.boxcarView', ['ngResource','spark.boxcar'])
        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/boxcar', {
                    templateUrl: 'vBoxcar/boxcarView.html',
                    controller: 'BoxcarViewController'
                });
            }])
        .controller('BoxcarViewController', ['$scope', 'boxcarDataService', '$log',  function ($scope, boxcarDataService, $log) {
                //Function to request tree data
                $scope.getTreeData = function () {
                    //Service call to request tree data
                    boxcarDataService.resource.children(
                            {boxcarid: $scope.boxcarid },
                            {},
                            function (val, response)
                            {
                                $log.log("Tree Data: ", val);
                                $scope.$broadcast('LoadBoxcarTreeData', val);
                            }
                    );
                };
                $scope.getTreeData();
            }])
 ;