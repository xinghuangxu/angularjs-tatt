/**
 * Primary controllers for the test planning module
 * 
 * @author Randall Crock & Leonx
 * @copyright 2014 NetApp, Inc.
 * @date 2014-07-07
 * 
 */

// Handler for controlling release selection dropdowns
testPlanner.controller('releaseNav',
        function ($scope, settingsData)
        {
            // TODO: Replace with data service
            // List of CQ trains
            $scope.releases = [
                {value: "LSIP2003", label: "Lancaster"}
            ];

            // TODO: Replace with data service
            // List of test stack layers for the currently selected train
            $scope.stackLayers = [
                {value: "pre-checkin", label: "Pre Check-in"},
                {value: "iop", label: "Interoperability (IOP)"},
                {value: "performance", label: "Performance and Benchmarking"},
                {value: "LSIP200492647", label: "Boxcar: Cloud Vaulting"},
                {value: "LSIP200562103", label: "Boxcar: UNMAP for thin provisioning"},
                {value: "LSIP200649410", label: "Boxcar: SSD Cache improvements"},
                {value: "LSIP200571335", label: "Boxcar: 64bit VxWorks"},
                {value: "LSIP200571754", label: "Boxcar: T10 PI improvements"},
                {value: "LSIP200571325", label: "Support Boxcar: Lancaster API/SDK"},
                {value: "trunk", label: "Integration (Trunk)"},
                {value: "cit", label: "Integration (CIT)"},
                {value: "rit", label: "Regression (RIT)"},
                {value: "rqa", label: "Maintenance (RQA)"},
                {value: "cet", label: "CET"},
            ];

            // TODO: Replace with data service
            // List of sub-layers for the currently selected test stack layer
            $scope.subLayers = [
                {value: "unit", label: "Unit Test"},
                {value: "module", label: "Module Test"},
                {value: "jenkins", label: "Jenkins build check-in Test"},
                {value: "performance", label: "Boxcar performance"},
                {value: "brt", label: "Boxcar BRT"},
                {value: "bst", label: "Boxcar BST"},
                {value: "e2e", label: "Boxcar E2E"}
            ];

            $scope.data = settingsData;

            // Watch for changes to the train and update the list of test stack layers
            $scope.$watch('data.phase.releaseName',
                    function ()
                    {
                        // TODO: Call relevant data service when value changes
                        $scope.data.phase.stackLayer = null;
                        $scope.data.phase.subLayer = null;
                    }
            );

            // Watch for changes to the test stack layer and update the list of sub-layers
            $scope.$watch('data.phase.stackLayer',
                    function ()
                    {
                        // TODO: Call relevant data service when value changes
                        $scope.data.phase.subLayer = null;
                    }
            );
        }
);

// Handler for hiding/showing different panes
testPlanner.controller('paneView',
        function ($scope)
        {
            $scope.paneGroups = [
                {
                    label: 'What',
                    name: 'what',
                    order: 0
                },
                {
                    label: 'Where',
                    name: 'where',
                    order: 1
                },
                {
                    label: 'When',
                    name: 'when',
                    order: 2
                }
            ];

            // Setup the defaults and configuration for each pane
            // Each template must contain a reference to the correct controller for that pane
            $scope.panes = [
                {
                    label: "Boxcar Scoping",
                    template: $scope.HTML_LOCATION + "/boxcar.html",
                    order: 0,
                    active: true,
                    group: 'what'
                },
                {
                    label: "SOW",
                    template: $scope.HTML_LOCATION + "/sow.html",
                    order: 1,
                    active: false,
                    group: 'what'
                },
                {
                    label: "Architecture Docs",
                    template: $scope.HTML_LOCATION + "/archDocs.html",
                    order: 2,
                    active: false,
                    group: 'what'
                },
                {
                    label: "Rally",
                    template: $scope.HTML_LOCATION + "/rally.html",
                    order: 3,
                    active: true,
                    group: 'when'
                },
                {
                    label: "Test Plan",
                    template: $scope.HTML_LOCATION + "/testPlan.html",
                    order: 4,
                    active: true,
                    group: 'what'
                },
                {
                    label: "Configuration Plan",
                    template: $scope.HTML_LOCATION + "/configPlan.html",
                    order: 5,
                    active: true,
                    group: 'where'
                },
                {
                    label: "ALM",
                    template: $scope.HTML_LOCATION + "/alm.html",
                    order: 6,
                    active: false,
                    group: 'what'
                },
                {
                    label: "WebLab",
                    template: $scope.HTML_LOCATION + "/weblab.html",
                    order: 7,
                    active: false,
                    group: 'where'
                }
            ];

            $scope.order = 'order';
            $scope.sortableOptions = {
                update: function (e, ui) {
                    var logEntry = $scope.panes.map(function (i) {
                        return i.label;
                    }).join(', ');
                    console.log('Update: ' + logEntry);
                },
                handle: '.sort-handle',
                scroll: false,
                cursor: 'move',
                placeholder: 'pane panel panel-primary'
            };

            $scope.active = function (item) {
                return item.active;
            };
        }
);

// Pane controllers - each is a separate entity.
// TODO: Export into separate files
testPlanner.controller('sow', function ($scope) {
});
testPlanner.controller('archDocs', function ($scope) {
});
testPlanner.controller('rally', function ($scope) {
});
testPlanner.controller('test', function ($scope) {
});
testPlanner.controller('config', function ($scope) {
});
testPlanner.controller('alm', function ($scope) {
});
testPlanner.controller('weblab', function ($scope) {
})


// Control settings popover
        .controller('settings',
                function ($scope, $modal, settingsData)
                {
                    $scope.data = settingsData;

                    // When 'Edit' is clicked, show the modal and hide the popover
                    $scope.edit = function () {
                        $modal(
                                {
                                    title: "Edit Settings",
                                    show: true,
                                    animation: "am-fade-and-slide-top",
                                    contentTemplate: "templates/settings/modal.html"
                                }
                        );

                        $scope.$hide();
                    };


                }
        )

// Control the settings modal
        .controller('settings.modal',
                function ($scope, settingsData)
                {
                    // Load settings and view information
                    $scope.data = settingsData;
                    $scope.tabs =
                            [
                                {
                                    title: 'Timeline',
                                    template: "templates/settings/timeline.html"
                                },
                                {
                                    title: 'ALM',
                                    template: "templates/settings/alm.html"
                                },
                                /*
                                 {
                                 title: 'Rally',
                                 template: "templates/settings/rally.html"
                                 }
                                 */
                            ];

                    // TODO: Configure saving when the dialog closes
                    $scope.$parent.$on('modal.hide', function () {
                        console.log("saving settings");
                    }
                    );
                }
        )

// Control the ALM tab of the settings modal
        .controller('settings.modal.alm',
                function ($scope, settingsData, almFolderData, $alert)
                {
                    // Retrieve the base data
                    $scope.alm = settingsData.alm;
                    almFolderData.getDomainList(
                            // Success
                                    function (val, response)
                                    {
                                        $scope.almDomains = val;
                                    },
                                    // Error
                                            function (response)
                                            {
                                                $alert({
                                                    title: "Error fetching ALM domains",
                                                    content: "There was an error fetching the list of ALM domains from the server.",
                                                    placement: 'top',
                                                    container: '.modal-dialog'
                                                });
                                            }
                                    );

                                    // Recursive function to add a set of children to the specified node ID
                                    var appendChildren = function (start, targetId, children)
                                    {
                                        for (child in start)
                                        {
                                            if (start[child].id === targetId)
                                            {
                                                start[child].children = children;
                                                break;
                                            }
                                            else
                                                appendChildren(start[child].children, targetId, children);
                                        }

                                        return start;
                                    };

                                    // Load a subfolder when the node is selected
                                    $scope.loadNode = function (data)
                                    {
                                        // Must have the event data
                                        // A quirk in jstree fires the event twice; once with, once without
                                        if (!data.event)
                                            return;

                                        // Update the selected path
                                        var parents = data.instance.get_path(data.node.id);
                                        $scope.alm.folder = "\\" + parents.join("\\");

                                        // Load the new data from the almFolderData service
                                        $scope.loading = true;
                                        almFolderData.getFolder(
                                                {alm_db: $scope.alm.domain, id: data.node.id},
                                        // Success
                                        function (val, response)
                                        {
                                            $scope.treeData = appendChildren($scope.treeData, data.node.id, val);
                                            $scope.loading = false;
                                        },
                                                // Error
                                                        function (response)
                                                        {
                                                            $alert({
                                                                title: "Error fetching ALM folders",
                                                                content: "There was an error fetching the list of subfolders for " + data.node.text + ".",
                                                                placement: 'top',
                                                                container: '.modal-dialog'
                                                            });
                                                            $scope.loading = false;
                                                        }
                                                );
                                            };

                                    // Watch for changes to the domain and update the tree accordingly
                                    $scope.$watch('alm.domain',
                                            function (domain, oldVal)
                                            {
                                                // Domain is not set, so exit without trying to fetch
                                                if (!domain)
                                                    return;

                                                // Reset the folder value if the domain has changed
                                                if (domain !== oldVal)
                                                    $scope.alm.folder = "";

                                                $scope.loading = true;
                                                almFolderData.getRootFolder(
                                                        // Get new domain root
                                                                {alm_db: domain},
                                                        // Success
                                                        function (val, response)
                                                        {
                                                            $scope.treeData = val;
                                                            $scope.loading = false;
                                                        },
                                                                // Error
                                                                        function (response)
                                                                        {
                                                                            // Get the readable name of the domain
                                                                            var domainName = "";
                                                                            for (var dm in $scope.almDomains)
                                                                            {
                                                                                if (dm.domain + '_db' == domain)
                                                                                {
                                                                                    domainName = dm.name;
                                                                                    break;
                                                                                }
                                                                            }

                                                                            // Warn the user
                                                                            $alert({
                                                                                title: "Error fetching ALM folders",
                                                                                content: "There was an error fetching the list of folders from the " + domainName + " domain.",
                                                                                placement: 'top',
                                                                                container: '.modal-dialog'
                                                                            });
                                                                            $scope.loading = false;
                                                                        }
                                                                );
                                                            }
                                                    );
                                                }
                                        );
