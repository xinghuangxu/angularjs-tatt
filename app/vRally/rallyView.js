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
        .controller('RallyViewController',function(){
            
            });	 