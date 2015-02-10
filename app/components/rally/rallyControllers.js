//Data fields controller
rally.controller("rallyController", ['$scope', function ($scope, $alert) {
        $scope.PopoverId = "popover.html";
        $scope.test = function () {

        };
        $scope.$on("RallyResponseHandle", function (event, data) {
            switch (data.status) {
                case 0:
                case 400:
                    $scope.emit('SessionTimeout',data);
                    break;
            }
        });
    }]);
rally.controller("rallyTree", function ($scope, dataService, myAuthentication, $alert, $log) {
    //Pulls in variables from factory for use
    $scope.authentication = myAuthentication;
    //Array to store all actions that can be undone
    $scope.undoArray = [];

    $scope.$on("RallyLoadTree", function (event, data) {
        $scope.getTreeData(data);
    });

    //Function to request tree data
    $scope.getTreeData = function (data) {

        $scope.load = true;
        //Service call to request tree data
        dataService.treeData(
                data,
                {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $scope.tree = val.data;
                        //Creates/Clears the undo array whenever a new tree is loaded
                        $scope.undoArray = [];
                        $scope.load = false;
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
                    $scope.$emit("RallyResponseHandle", response);
                }
        );
    };

    //Function to move a node
    $scope.moveNode = function (data) {
        var moveNodeData = {node: data.node, parent: data.parent};
        //Service call to change the parent of a node
        dataService.dragdrop(
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
                    }
                },
                function (response)
                {
                    $scope.$emit("RallyResponseHandle", response);
                }
        );
    }

    //Function that currently undos any move node action. Note: This function is compatible to add add/delete undo functions in the future
    $scope.undoAction = function () {
        $scope.undoData = $scope.undoArray.pop();
        $log.log('Undo Data', $scope.undoData);
        $log.log('Undo Array', $scope.undoArray);
        //Service Call to perform an undo action
        dataService.undo(
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
                    $scope.$emit("RallyResponseHandle", response);
                }
        );
    };

    //Function to deliver the edit enable checkbox value to the jstree directive
    $scope.editEnable = function () {
        return $scope.enabled;
    };

    //Function to logout of rally plug-in
    $scope.logOut = myAuthentication.logout;

    //Function to store selected jstree node information
    $scope.storeNode = function (data) {
        myAuthentication.selectedNode.nodeID = data.nodeID;
        myAuthentication.selectedNode.children = data.children;
        myAuthentication.selectedNode.name = data.name;
    };

    //Function to confirm a successful delete.
    $scope.deleteNode = function () {
        //Resets the flag variable for delete so the next delete can be called
        myAuthentication.deleteSuccess = false;
    };

    $scope.nullEditInfo = function () {
        myAuthentication.actionNode = null;
        myAuthentication.editInfo.nodeID = null;
        myAuthentication.editInfo.name = null;
        myAuthentication.editInfo.archID = null;
        myAuthentication.editInfo.iteration = null;
        myAuthentication.editInfo.icon = null;
        myAuthentication.editInfo.blocked = null;
    };
});

//Controller for the popover	
rally.controller("rallyPopoverCtrl", function ($scope, dataService, myAuthentication, $modal, $alert, $sce) {
    //sets the submitted variable to false to reset the error visualizations
    //$scope.submitted = false;

    //Function when the add button is used
    $scope.addButton = function () {
        $scope.newNodeID = myAuthentication.selectedNode.nodeID;
        $scope.newNode = {};
        $scope.newNode.owner = $scope.specificOwner;
        $scope.newNode.release = $scope.releaseChosen;
        //$scope.submitted = false;
        $scope.newNode.state = "Defined";

        //Pops the last element of the release and iteration lists since the Rally server does not accept the "All" option
        var noAllRelease = $scope.releaseList.indexOf('All');
        $scope.exactReleaseList = $scope.releaseList.slice(0);
        $scope.exactReleaseList.splice(noAllRelease, 1);

        var noAllIteration = $scope.iterationList.indexOf('All');
        $scope.exactIterationList = $scope.iterationList.slice(0);
        $scope.exactIterationList.splice(noAllIteration, 1);
        //A Testing console log
        //console.log("PCtrl Node ID: ", $scope.newNodeID);

        var addModal = $modal({
            contentTemplate: 'components/rally/partial/_addForm.html',
            scope: $scope,
            show: true
        });

        $scope.submit = function (submitType) {

            dataService.addNode(
                    {
                        project: $scope.projectChosen,
                        title: $scope.newNode.title,
                        owner: $scope.newNode.owner,
                        state: $scope.newNode.state,
                        release: $scope.newNode.release,
                        points: $scope.newNode.points,
                        iteration: $scope.newNode.iteration,
                        description: $scope.newNode.description,
                        newNodeID: $scope.newNodeID,
                        arch: $scope.newNode.arch
                    },
            {},
                    function (val, response)
                    {
                        if (val.data == 'The domain is not registered') {
                            alert("Rally service is unavailable");
                        }
                        else {
                            //This if statement determines if the front-end will create a new node in jstree. If the new user story does not have the same release as the current tree that is displayed, the user story will not be visualized.
                            if ($scope.newNode.release == $scope.releaseChosen) {
                                //These send the necessary parameters to the directive so the new user story can be created. Note: Currently "has" field (from the tree data nodes) is not sent because it is not currently needed.
                                console.log("Add Data: ", val.data);
                                myAuthentication.actionNode = $scope.newNodeID;
                                myAuthentication.addNode.nodeID = val.data.ID;
                                myAuthentication.addNode.name = $scope.newNode.title;
                                myAuthentication.addNode.archID = $scope.newNode.arch;
                                myAuthentication.addNode.iteration = $scope.newNode.iteration;
                                myAuthentication.addNode.icon = val.data.icon;
                                myAuthentication.addNode.blocked = val.data.Blocked;
                            }
                            switch (submitType) {

                                case "close":
                                    $(addModal).modal('hide');
                                    break;
                                case "new":
                                    $scope.newNode.title = null;
                                    $scope.newNode.state = null;
                                    $scope.newNode.points = null;
                                    $scope.newNode.iteration = null;
                                    $scope.newNode.arch = null;
                                    $scope.newNode.description = '';
                                    break;
                            }
                            ;
                            successMsg = 'User story added under Release: ' + $scope.newNode.release;
                            $alert({title: 'Success:', content: successMsg, container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                        }
                    },
                    //error messages
                            function (response)
                            {
                                $scope.$emit("RallyResponseHandle", response);
                            }
                    )

                }

    };

    //Function when the info button is used
    $scope.infoButton = function () {
        $scope.nodeID = myAuthentication.selectedNode.nodeID;
        $scope.load = true;
        $scope.nopoints = false;
        //A Testing console log
        //console.log("PCtrl Node ID: ", $scope.nodeID);
        //Call to get metadata of node
        dataService.metadata(
                {
                    input: $scope.nodeID
                },
        {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $scope.modalData = val.data;
                        //allows the description box to visualize html 
                        $scope.modalData.description = $sce.trustAsHtml(val.data.description);
                        console.log(val.data);
                        var myModal = $modal({
                            contentTemplate: 'components/rally/partial/_modal.html',
                            scope: $scope,
                            show: true
                        });
                    }
                },
                function (response)
                {
                    $scope.$emit("RallyResponseHandle", response);
                }
        );

        dataService.EQI(
                {
                    input: $scope.nodeID
                },
        {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $scope.nopoints = false;
                        $scope.eqiData = val.data;
                        console.log("EQI Data: ", val.data);
                        $scope.percentage = 100 * $scope.eqiData.Accepted / $scope.eqiData.Planned;
                        console.log("Percentage: ", $scope.percentage);
                        $scope.load = false;
                    }
                },
                function (response)
                {
                    $scope.$emit("RallyResponseHandle", response);
                }
        )
    }

    //Function when the edit button is used
    $scope.editButton = function () {
        $scope.nodeID = myAuthentication.selectedNode.nodeID;
        $scope.children = myAuthentication.selectedNode.children;

        //Pops the last element of the release and iteration lists since the Rally server does not accept the "All" option
        var noAll = $scope.releaseList.indexOf('All');
        $scope.exactReleaseList = $scope.releaseList.slice(0);
        $scope.exactReleaseList.splice(noAll, 1);
        $scope.exactIterationList = $scope.iterationList.slice(0);
        $scope.exactIterationList.pop();

        //A Testing console log
        //console.log("PCtrl Node ID: ", $scope.nodeID);
        //Call to get metadata of node

        if ($scope.children) {
            $scope.parent = true;
        } else {
            $scope.parent = false;
        }

        dataService.metadata(
                {
                    input: $scope.nodeID
                },
        {},
                function (val, response)
                {
                    if (val.data == 'The domain is not registered') {
                        alert("Rally service is unavailable");
                    }
                    else {
                        $scope.editData = val.data;
                        $scope.staticTitle = val.data.Title;
                        $scope.staticArch = val.data.arch;
                        $scope.staticState = val.data.state;
                        $scope.staticIteration = val.data.iteration
                        console.log("Edit Data: ", $scope.editData);
                        $scope.editData.description = val.data.description;
                    }
                },
                //Error cases
                        function (response)
                        {
                            $scope.$emit("RallyResponseHandle", response);
                        }
                )

                var editModal = $modal({
                    contentTemplate: 'components/rally/partial/_editForm.html',
                    scope: $scope,
                    show: true
                });


                $scope.esubmit = function (esubmitType) {

                    dataService.updateNode(
                            {
                                project: $scope.projectChosen,
                                title: $scope.editData.Title,
                                owner: $scope.editData.owner,
                                points: $scope.editData.points,
                                state: $scope.editData.state,
                                release: $scope.editData.release,
                                iteration: $scope.editData.iteration,
                                description: $scope.editData.description,
                                newNodeID: $scope.nodeID,
                                arch: $scope.editData.arch
                            },
                    {},
                            function (val, response)
                            {
                                if (val.data == 'The domain is not registered') {
                                    alert("Rally service is unavailable");
                                }
                                else {

                                    if ($scope.editData.release !== $scope.releaseChosen && !$scope.children && ($scope.releaseChosen !== 'All')) {

                                        myAuthentication.actionNode = $scope.nodeID;
                                        myAuthentication.deleteSuccess = true;
                                        //console.log ('release does not equal releaseChosen');
                                        newRelease = 'User story located in Release: ' + $scope.editData.release;
                                        $alert({title: 'Success:', content: newRelease, container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                                    }
                                    if ($scope.editData.Title !== $scope.staticTitle || $scope.editData.iteration !== $scope.staticIteration || $scope.editData.state !== $scope.staticState || $scope.editData.arch !== $scope.staticArch) {

                                        console.log("Node ID changed?: ", $scope.nodeID);
                                        myAuthentication.actionNode = $scope.nodeID;
                                        myAuthentication.editInfo.nodeID = $scope.nodeID;
                                        myAuthentication.editInfo.name = $scope.editData.Title;
                                        myAuthentication.editInfo.archID = $scope.editData.arch;
                                        myAuthentication.editInfo.iteration = $scope.editData.iteration;
                                        myAuthentication.editInfo.icon = val.data.icon;
                                        myAuthentication.editInfo.blocked = val.data.Blocked;

                                        console.log("Action node: ", myAuthentication.actionNode);
                                    }

                                    console.log(esubmitType);
                                    switch (esubmitType) {
                                        case "close":
                                            $(editModal).modal('hide');
                                            if ($scope.editData.release == $scope.releaseChosen) {
                                                $alert({title: 'Success:', content: 'User Story Edited', container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                                            }
                                            break;
                                        case "save":
                                            $alert({title: 'Success:', content: 'User Story Edited', container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                                            break;
                                    }
                                    ;
                                }
                            },
                            function (response)
                            {
                                $scope.$emit("RallyResponseHandle", response);
                            }
                    )
                }

            }

    //Function when the delete button is used
    $scope.deleteButton = function () {
        //Needed variables to check if user story can be deleted
        $scope.nodeID = myAuthentication.selectedNode.nodeID;
        $scope.children = myAuthentication.selectedNode.children;
        $scope.name = myAuthentication.selectedNode.name;

        //Cases for deleting nodes
        if (!$scope.children) {
            //Modal to display confirmation message
            var deleteModal = $modal({
                contentTemplate: 'components/rally/partial/_deleteModal.html',
                scope: $scope,
                show: true
            });
        } else {
            $alert({title: 'Invalid Delete:', content: 'Cannot be deleted because user story has children', container: '#alert-location', type: 'danger', duration: 5, dismissable: false});
        }
        //Function that is run when the delete is confirmed
        $scope.deleteConfirm = function () {
            $(deleteModal).modal('hide');
            dataService.deleteNode(
                    {
                        input: $scope.nodeID
                    },
            {},
                    function (val, response)
                    {
                        if (val.data == 'The domain is not registered') {
                            alert("Rally service is unavailable");
                        }
                        else {
                            //Variable that lets the directive know to delete the user story
                            myAuthentication.actionNode = $scope.nodeID;
                            myAuthentication.deleteSuccess = true;
                            $alert({title: 'Success:', content: 'Node Deleted', container: '#alert-location', type: 'success', duration: 5, dismissable: false});
                        }
                    },
                    function (response)
                    {
                        $scope.$emit("RallyResponseHandle", response);
                    }
            )

        };
    };

});