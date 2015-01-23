//JStree directive
boxcar.directive('boxcarJstree', [ '$templateCache', '$compile', function ( $templateCache, $compile)
    {
        return {
            restrict: 'E',
            link: function (scope, element, attrs) {
                //When this flag is true, the move_node event is skipped

              
                scope.$watch(attrs.ngModel, function ()
                {
                    var to = false;
                    $('#treeSearch').keyup(function () {
                        if (to) {
                            clearTimeout(to);
                        }
                        to = setTimeout(function () {
                            var v = $('#treeSearch').val();
                            $(element).jstree(true).search(v);
                        }, 250);
                    });

                    //Destroys last instance of jstree so a new one can be created. Ideally jstree refresh should be used, but functionality of refresh has not worked
                    $(element).jstree("destroy");

                    //Event that fires when a node is moved
                    $(element).bind('move_node.jstree', function (e, data) {
                        console.log('Move Data: ', data);
                        //This sends information to the controller (to send to the back-end) if a node is moved (excluding re-ordering amongst siblings)
                        if (data.old_parent != data.parent && undoFlag == false) {
                            scope.nodeInfo = {node: '', parent: '', oldParent: ''};
                            scope.nodeInfo.node = data.node.id;
                            scope.nodeInfo.parent = data.node.parent;
                            scope.nodeInfo.oldParent = data.old_parent;
                            scope.nodeInfo.oldPosition = data.old_position;
                            scope.$apply(attrs.moveNode);
                            console.log(scope.nodeInfo);
                        } else {
                            undoFlag = false;
                        }

                    })


                    $(element).bind("select_node.jstree", function (e, data) {
                        scope.selectInfo = {nodeID: data.node.id, children: data.node.children.length, name: data.node.text};
                        scope.$apply(attrs.storeNode);
                        //Disabled: This is a testing console log message
                        console.log("Node ID: ", data.node.id);

                    })

                    //Loads the popover template
                    var popoverContent = $templateCache.get("boxcarPopover.html");
                    //Gives the popover a controller
                    var finalContent = $compile("<div>" + popoverContent + "</div>")(scope);
                    //Options of popover
                    var options = {
                        html: true,
                        content: finalContent,
                        title: false,
                        placement: 'top',
                        trigger: 'focus'
                    };

                    //Current method for binding the popover
                    $(element).on("hover_node.jstree", function (e, data, node) {
                        $(".jstree-hovered").popover(options);
                    })

                    //jstree format setup
                    $(element).jstree(
                            {
                                //"state" plugin currently disabled since it does not work alongside our select node function
                                plugins: ["themes", "search", "dnd", "crrm", "ui"],
                                search: {
                                    "case_sensitive": false,
                                    //only displays matches found in search bar
                                    "show_only_matches": true,
                                    //fuzzy set to false so search looks for exact matches
                                    "fuzzy": false
                                },
                                core: {
                                    multiple: false,
                                    themes: {
                                        "theme": "default",
                                        "icons": true,
                                        "dots": false
                                    },
                                    //Allows for nodes to be dragged
                                    check_callback: function (operation, node, node_parent, node_position, more) {
                                        /*console.log("op1: ", operation);
                                         console.log('node1: ', node);
                                         console.log('par1: ', node_parent);
                                         console.log('pos1: ', node_position);
                                         console.log('more1: ', more);
                                         console.log('----------------------------------------------------------------------------------------------');*/
                                        if (operation == "delete_node" && !scope.boxcarDataService.deleteSuccess && !scope.boxcarDataService.editRelease) {
                                            return false;
                                        } else if (operation == "copy_node") { //Temporary fix for Ctrl functionality when user story is in the process of being dragged. (Prevents copy feature)
                                            return false;
                                        } else {
                                            return scope.editEnable();
                                        }
                                    },
                                    data: scope[attrs.ngModel]
                                }
                            }
                    );

                }, true);

            }

        };
    }]);