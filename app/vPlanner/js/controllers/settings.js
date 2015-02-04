angular.module("controllers")

// Control settings popover
.controller('settings', 
    function($scope, $modal, settingsData) 
    { 
        $scope.data = settingsData;
        
        // When 'Edit' is clicked, show the modal and hide the popover
        $scope.edit = function() { 
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
    function($scope, settingsData)
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
        $scope.$parent.$on('modal.hide', function() { 
            console.log("saving settings");
            }
        );
    }
)

// Control the ALM tab of the settings modal
.controller('settings.modal.alm', 
    function($scope, settingsData, almFolderData, $alert)
    {
        // Retrieve the base data
        $scope.alm = settingsData.alm;
        almFolderData.getDomainList(
            // Success
            function(val, response)
            {
                $scope.almDomains = val;
            },
            // Error
            function(response)
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
        var appendChildren = function(start, targetId, children)
        {
            for(child in start)
            {
                if(start[child].id === targetId)
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
        $scope.loadNode = function(data)
        {
            // Must have the event data
            // A quirk in jstree fires the event twice; once with, once without
            if(!data.event)
                return;
            
            // Update the selected path
            var parents = data.instance.get_path(data.node.id);
            $scope.alm.folder =  "\\" + parents.join("\\");

            // Load the new data from the almFolderData service
            $scope.loading = true;
            almFolderData.getFolder(
                    {alm_db: $scope.alm.domain, id: data.node.id},
                    // Success
                    function(val, response)
                    {
                        $scope.treeData = appendChildren($scope.treeData, data.node.id, val);
                        $scope.loading = false;
                    },
                    // Error
                    function(response)
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
            function(domain, oldVal)
            { 
                // Domain is not set, so exit without trying to fetch
                if(!domain)
                    return;
                
                // Reset the folder value if the domain has changed
                if(domain !== oldVal)
                    $scope.alm.folder = "";
                
                $scope.loading = true;
                almFolderData.getRootFolder(
                    // Get new domain root
                    {alm_db: domain},
                    // Success
                    function(val, response)
                    {
                        $scope.treeData = val;
                        $scope.loading = false;
                    },
                    // Error
                    function(response)
                    {
                        // Get the readable name of the domain
                        var domainName = "";
                        for(var dm in $scope.almDomains)
                        {
                            if(dm.domain + '_db' == domain)
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
