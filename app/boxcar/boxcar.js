'use strict';

var boxcar = angular.module('spark.boxcar', ['ngAnimate', 'ngSanitize', 'mgcrea.ngStrap', 'ngResource', 'colorpicker.module', 'wysiwyg.module', 'ngRoute'])
        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/boxcar', {
                    templateUrl: 'boxcar/boxcar.html',
                    controller: 'BoxcarController'
                });
            }])
        .controller('BoxcarController', ['$scope', 'boxcarDataService', '$log', 'boxcarContainer', '$templateCache', '$compile', function ($scope, boxcarDataService, $log, boxcarContainer, $templateCache, $compile) {
                $scope.boxcarDataService = boxcarDataService;

                function updateTree(classifier) {
                    if (classifier) {
                        $scope.tree = boxcarContainer.toTreeFormat(classifier);
                        $scope.$broadcast("ShowBoxcarTree", $scope.tree);
                    }
                }
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
                                updateTree("pr")
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
                $scope.selectedClassifier = $scope.classifiers[0];
                $scope.$watch('selectedClassifier', function (newValue, oldValue) {
                    updateTree(newValue.value);
                });

                var leafInfoDialog = $templateCache.get("leafInfoDialog.html");
                //Function when the add button is used
                $scope.info = function () {
                    var data = $scope.selectInfo;
                    $scope.parent=data.parent;
                    var finalContent = $compile("<div>" + leafInfoDialog + "</div>")($scope);
                    var dialogBox = finalContent.attr("title", data.text);
                    dialogBox.dialog({
                        width: 500,
                        height:300
                    });
                };

                $scope.export = function () {

                };

            }]);