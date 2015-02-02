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
        .factory('boxcarDataService', ["$resource", function ($resource) {
                var boxcarDataService = {};
                boxcarDataService.resource = $resource('php/boxcar.php', {},
                        {
                            children: {method: 'GET', params: {}, timeout: '60000', isArray: true}
                        }
                );
                return boxcarDataService;
            }]);