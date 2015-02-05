// initializes the module for the app
angular.module("spark.rallyView", ['spark.rally'])
        .config(['$routeProvider',
            function ($routeProvider) {
                $routeProvider.
                        when('/rally', {
                            templateUrl: 'vRally/rallyView.html',
                            controller: "RallyViewController"
                        });
            }])
        .controller('RallyViewController', function ($scope) {
            var data = {
                project: "Spark Sandbox",
                release: "All",
                iteration: ""
            };
            $scope.broadcast=function(){
                 $scope.$broadcast("RallyLoadTree",data);
            };
           
        });	 