'use strict';

var boxcar = angular.module('spark.boxcar', ['ngAnimate', 'ngSanitize', 'mgcrea.ngStrap', 'ngResource', 'colorpicker.module', 'wysiwyg.module', 'ngRoute'])

        .config(['$routeProvider', function ($routeProvider) {
                $routeProvider.when('/boxcar', {
                    templateUrl: 'boxcar/boxcar.html',
                    controller: 'BoxcarController'
                });
            }])

        .controller('BoxcarController', ['$scope','boxcarDataService','$log', function ($scope,boxcarDataService,$log) {
                
                $scope.boxcarDataService=boxcarDataService;
                //Function to request tree data
                $scope.getTreeData = function () {

                    $scope.load = true;
                    //Service call to request tree data
                    boxcarDataService.resource.children(
                            {
                                boxcarid: $scope.boxcarid,
                            },
                    {},
                            function (val, response)
                            {
                                if (val.data == 'The domain is not registered') {
                                    alert("Rally service is unavailable");
                                }
                                else {
                                    $log.log("Tree Data: ", val.data);
                                    $scope.tree = val.data;
                                    $scope.load = false;
                                    //Creates/Clears the undo array whenever a new tree is loaded

                                    //Currently not in use: Functions to create an iteration list for use in the iteration filter
                                    /*   $scope.iterationList = [];
                                     for(c=0; c < $scope.tree.length; c++){
                                     if($scope.tree[c].Iteration){
                                     $scope.iterationList.push($scope.tree[c].Iteration);
                                     }	
                                     }
                                     $scope.iterationList = $scope.iterationList.filter( onlyUnique );
                                     $scope.iterationList.sort();
                                     $scope.iterationList.push("All");
                                     function onlyUnique(value, index, self) { 
                                     return self.indexOf(value) === index;
                                     } 
                                     
                                     timeoutCount = 0;  */
                                }
                            },
                            function (response)
                            {
                               
                            }
                    );
                }
                $scope.getTreeData();

                //Function to move a node
                $scope.moveNode = function (data) {
                    var moveNodeData = {node: data.node, parent: data.parent};
                    //Service call to change the parent of a node
                    boxcarDataService.dragdrop(
                            {
                                input: moveNodeData
                            },
                    {},
                            function (val, response)
                            {
                                if (val.data == 'The domain is not registered') {
                                    alert("Rally service is unavailable");
                                }
                                else {
                                    $log.log("Move Successful");
                                    $alert({title: 'Success:', content: 'User Story Moved', container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                                    //On successful node move, information is stored for undo action
                                    var undoNodeData = {
                                        input: {node: data.node, parent: data.oldParent},
                                        input_type: 'dragdrop',
                                        position: data.oldPosition
                                    };
                                    $scope.undoArray.push(undoNodeData);
                                    //A Testing console log
                                    //console.log("UndoArray: ", $scope.undoArray);
                                    $scope.load = false;
                                    timeoutCount = 0;
                                }
                            },
                            function (response)
                            {
                                //Error cases
                                switch (response.status) {
                                    case 0:
                                        if (timeoutCount < maxTimeoutAttempts) {
                                            $log.log("PHP timeout: Retrying connection...");
                                            $alert({title: 'PHP Timeout:', content: 'Retrying connection...', container: '#alert-location', type: 'warning', duration: 5, dismissable: false});
                                            $scope.getProjectList();
                                            timeoutCount++;
                                        } else {
                                            $log.log("Timed Out: Could not reach php server");
                                            $alert({title: 'Timed Out:', content: 'Could not reach php server', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                            timeoutCount = 0;
                                        }
                                        break;
                                    case 400:
                                        $log.log("Session Timeout");
                                        $alert({title: 'Session Timeout:', content: 'Please relogin', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                        delete $scope.tree;
                                        delete $scope.releaseList;
                                        delete $scope.projectList;
                                        delete $scope.projectChosen;
                                        delete $scope.releaseChosen;
                                        boxcarDataService.loginView = true;
                                        boxcarDataService.dataView = false;
                                        break;
                                }
                                $scope.load = false;
                            }
                    );
                }

                //Function that currently undos any move node action. Note: This function is compatible to add add/delete undo functions in the future
                $scope.undoAction = function () {
                    $scope.undoData = $scope.undoArray.pop();
                    $log.log('Undo Data', $scope.undoData);
                    $log.log('Undo Array', $scope.undoArray);
                    //Service Call to perform an undo action
                    boxcarDataService.undo(
                            {
                                input: $scope.undoData.input,
                                input_type: $scope.undoData.input_type
                            },
                    {},
                            function (val, response)
                            {
                                if (val.data == 'The domain is not registered') {
                                    alert("Rally service is unavailable");
                                }
                                else {
                                    //Deletes the undo Data. This is because if the user does an action undos and does the same action and undos again, the directive will not catch the undoData watch since the undoData would not have technically changed. This makes sure the watch catches all instances of the undo button click. Unfortunately deleting undoData also triggers watch however it will not try and move a node due to the directive code. This code can/should be updated in the future as needed.
                                    delete $scope.undoData;
                                    $log.log('Undo Successful');
                                    $alert({title: 'Success:', content: 'Move Undone', container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                                }
                            },
                            function (response)
                            {
                                //Error cases
                                switch (response.status) {
                                    case 0:
                                        if (timeoutCount < maxTimeoutAttempts) {
                                            $log.log("PHP timeout: Retrying connection...");
                                            $alert({title: 'PHP Timeout:', content: 'Retrying connection...', container: '#alert-location', type: 'warning', duration: 5, dismissable: false});
                                            $scope.getProjectList();
                                            timeoutCount++;
                                        } else {
                                            $log.log("Timed Out: Could not reach php server");
                                            $alert({title: 'Timed Out:', content: 'Could not reach php server', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                            timeoutCount = 0;
                                        }
                                        break;
                                    case 400:
                                        $log.log("Session Timeout");
                                        $alert({title: 'Session Timeout:', content: 'Please relogin', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
                                        delete $scope.tree;
                                        delete $scope.releaseList;
                                        delete $scope.projectList;
                                        delete $scope.projectChosen;
                                        delete $scope.releaseChosen;
                                        boxcarDataService.loginView = true;
                                        boxcarDataService.dataView = false;
                                        break;
                                }
                            }
                    );
                };

                //Function to deliver the edit enable checkbox value to the jstree directive
                $scope.editEnable = function () {
                    return $scope.enabled;
                };


                //Function to store selected jstree node information
                $scope.storeNode = function (data) {
                    boxcarDataService.selectedNode.nodeID = data.nodeID;
                    boxcarDataService.selectedNode.children = data.children;
                    boxcarDataService.selectedNode.name = data.name;
                }

                //Function to confirm a successful delete.
                $scope.deleteNode = function () {
                    //Resets the flag variable for delete so the next delete can be called
                    boxcarDataService.deleteSuccess = false;
                }

                $scope.nullEditInfo = function () {
                    boxcarDataService.actionNode = null;
                    boxcarDataService.editInfo.nodeID = null;
                    boxcarDataService.editInfo.name = null;
                    boxcarDataService.editInfo.archID = null;
                    boxcarDataService.editInfo.iteration = null;
                    boxcarDataService.editInfo.icon = null;
                    boxcarDataService.editInfo.blocked = null;
                }
            }]);