'use strict';

angular.module('spark.doc', ['ngRoute'])

.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/doc', {
    templateUrl: 'doc/doc.html',
    controller: 'DocController'
  });
}])

.controller('DocController', [function() {

}]);