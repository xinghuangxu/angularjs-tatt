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
    function($scope, settingsData)
    {
        // TODO: Replace with data service
        // List of CQ trains
        $scope.releases = [
                           { value: "LSIP2003", label: "Lancaster" }
                      ];
        
        // TODO: Replace with data service
        // List of test stack layers for the currently selected train
        $scope.stackLayers = [
                              { value: "pre-checkin", label: "Pre Check-in"},
                              { value: "iop", label: "Interoperability (IOP)" },
                              { value: "performance", label: "Performance and Benchmarking" },
                              { value: "LSIP200492647", label: "Boxcar: Cloud Vaulting" },
                              { value: "LSIP200562103", label: "Boxcar: UNMAP for thin provisioning" },
                              { value: "LSIP200649410", label: "Boxcar: SSD Cache improvements" },
                              { value: "LSIP200571335", label: "Boxcar: 64bit VxWorks" },
                              { value: "LSIP200571754", label: "Boxcar: T10 PI improvements" },
                              { value: "LSIP200571325", label: "Support Boxcar: Lancaster API/SDK" },
                              { value: "trunk", label: "Integration (Trunk)" },
                              { value: "cit", label: "Integration (CIT)" },
                              { value: "rit", label: "Regression (RIT)" },
                              { value: "rqa", label: "Maintenance (RQA)" },
                              { value: "cet", label: "CET" },
                      ];
        
        // TODO: Replace with data service
        // List of sub-layers for the currently selected test stack layer
        $scope.subLayers = [
                            { value: "unit", label: "Unit Test" },
                            { value: "module", label: "Module Test" },
                            { value: "jenkins", label: "Jenkins build check-in Test" },
                            { value: "performance", label: "Boxcar performance" },
                            { value: "brt", label: "Boxcar BRT" },
                            { value: "bst", label: "Boxcar BST" },
                            { value: "e2e", label: "Boxcar E2E" }
                      ];
        
        $scope.data = settingsData;
        
        // Watch for changes to the train and update the list of test stack layers
        $scope.$watch('data.phase.releaseName',
            function() 
            { 
                // TODO: Call relevant data service when value changes
                $scope.data.phase.stackLayer = null; 
                $scope.data.phase.subLayer = null; 
            }
        );
        
        // Watch for changes to the test stack layer and update the list of sub-layers
        $scope.$watch('data.phase.stackLayer', 
            function() 
            { 
                // TODO: Call relevant data service when value changes
                $scope.data.phase.subLayer = null; 
            }
        );
    }
);

// Handler for hiding/showing different panes
testPlanner.controller('paneView', 
    function($scope)
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
        $scope.panes =  [
                    {
                        label: "Boxcar Scoping",
                        template: "templates/boxcar.html",
                        order: 0,
                        active: true,
                        group: 'what'
                    },
                    { 
                        label: "SOW", 
                        template: "templates/sow.html", 
                        order: 1, 
                        active: false,
                        group: 'what'
                    },
                    { 
                        label: "Architecture Docs", 
                        template: "templates/archDocs.html", 
                        order: 2, 
                        active: false,
                        group: 'what'
                    },
                    { 
                        label: "Rally", 
                        template: "templates/rally.html", 
                        order: 3, 
                        active: true,
                        group: 'when'
                    },
                    { 
                        label: "Test Plan", 
                        template: "templates/testPlan.html", 
                        order: 4, 
                        active: true,
                        group: 'what'
                    },
                    { 
                        label: "Configuration Plan", 
                        template: "templates/configPlan.html", 
                        order: 5, 
                        active: true,
                        group: 'where'
                    },
                    { 
                        label: "ALM", 
                        template: "templates/alm.html", 
                        order: 6, 
                        active: false,
                        group: 'what'
                    },
                    { 
                        label: "WebLab", 
                        template: "templates/weblab.html", 
                        order: 7, 
                        active: false,
                        group: 'where'
                    }    
                ];
        
        $scope.order = 'order';
        $scope.sortableOptions = {
            handle: '.sort-handle',
            scroll: false,
            cursor: 'move',
            placeholder: 'pane panel panel-primary'
        };
        
        $scope.active = function(item) { return item.active; };
    }
);

// Pane controllers - each is a separate entity.
// TODO: Export into separate files
testPlanner.controller('sow', function($scope) {});
testPlanner.controller('archDocs', function($scope) {});
testPlanner.controller('rally', function($scope) {});
testPlanner.controller('test', function($scope) {});
testPlanner.controller('config', function($scope) {});
testPlanner.controller('alm', function($scope) {});
testPlanner.controller('weblab', function($scope) {});