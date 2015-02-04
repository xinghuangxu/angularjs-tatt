// initializes the module for the app
 angular.module("spark.plannerView",['spark.testPlanner'])
        .config(['$routeProvider',
            function ($routeProvider) {
                $routeProvider.
                        when('/planner', {
                            templateUrl: 'vPlanner/plannerView.html',
                            controller: "PlannerViewController"
                        });
            }])
        .controller('PlannerViewController',function($scope){
            $scope.HTML_LOCATION="components/testPlanner/_partial";
            });// Empty generic controller, can use if needed
