/**
 * Spark specific UI directives
 * @author Randall Crock
 */

angular.module("spark.ui", [])

// Create a set of resizable items using jquery UI
.directive("sparkResizable", [
    function()
    {
        
        // Get the width an element should use when added
        var getDefaultWidth = function(elem)
        {
            var parentWidth = elem.parent().width();
            var children = elem.siblings().length + 1;
            var childrenWidth = 0;
            
            // Inspect the siblings of the new element to see if they have been resized, and exclude them if they have
            $.each(elem.siblings(), function(ind, obj){
                if(angular.element(obj).scope().resized)
                {
                    childrenWidth += $(obj).width;
                    children--;
                }
            });
            
            return (parentWidth - childrenWidth) / children;
        };
        
        return {
            // Must be applied to each element in the container as an attribute
            restrict: 'A', 
            link: function (scope, element, attrs)
            {
                // Default options for the jquery UI resizable()
                var opts = {
                    handles: "e",
                    ghost: true,
                    minWidth: 150,
                    
                    // Don't set the 'left' css property to protect alignments
                    // We're only really interested in the 'width' property
                    resize: function(e, ui) { 
                        ui.element.css('left', "");
                    },
                    
                    // Resize the panel next to this when the resizing ends
                    // This means each pair will have a constant width, so
                    // making one larger shrinks the other
                    stop: function(e, ui)
                    {
                        var nextWid = ui.element.next().width();
                        var widDiff = ui.size.width - ui.originalSize.width;
                        ui.element.next().width(nextWid-widDiff);
                        scope.resized = true;
                        angular.element(ui.element.next()).scope().resized = true;
                    }
                    
                };
                
                // Check to see if we are the last element, and disable the resize
                // and enable the previous element if it exists.
                
                // This will still work since the resize is attached to the left-
                // hand sibling
                if($(element).is(":last-child"))
                {
                    $(element).prev().resizable('option', 'disabled', false);
                    opts.disabled = true;
                }
                
                // Create the resizable and adjust the siblings as needed
                $(element).resizable(opts);
                var width = getDefaultWidth(element);
                $(element).width(width);
                $.each($(element).siblings(), 
                        function(ind, obj) 
                        { 
                            // Only change widths for other items which haven't been resized
                            if(!angular.element(obj).scope().resized)
                                $(obj).width(width); 
                        }
                );
            }
        };
    }
])

// Create a jstree display element
.directive("sparkJstree", [
    function()
    {
        // Default options for the tree
        var defaultConfig = {
            plugins: ['themes', 'dnd', 'search'],
            core:
            {
                themes:
                {
                    theme: 'default',
                    dots: false,
                    icons: true
                },
                check_callback: function(operation, node, node_parent, node_position, more)
                {
                    return operation === 'rename_node' ? true : true;
                },
                search : {
                    "case_sensitive" : false,
                    "show_only_matches" : true,
                    "fuzzy": false
                }
            }
        };
        
        return {
            restrict: 'E',
            scope: {
                dataTheme: '@',
                dataSearchTerm: '=?',
                selectCallback: '&onSelect',
                ngModel: '='
            },
            link: function(scope, element, attrs)
            {
                // Set the theme, if it is defined
                if(scope.dataTheme)
                    defaultConfig.themes.theme = scope.dataTheme;
                
                // Setup the selection callback, if it is defined
                if(typeof scope.selectCallback === 'function')
                {
                    $(element).on('select_node.jstree', 
                            function(e, data)
                            {
                                scope.selectCallback({data: data});
                                $(element).jstree(true).open_node(data.node.id);
                            });
                }
                
                // Initialize the tree
                $(element).jstree(defaultConfig);
                
                // Watch for changes and render as needed
                scope.$watch('ngModel', 
                    function(data)
                    {
                        $(element).jstree(true).settings.core.data = data;
                        $(element).jstree(true).refresh();
                    },
                    true
                );
                
                // Watch for the search term to change
                scope.$watch('dataSearchTerm',
                    function(data)
                    {
                        $(element).jstree(true).search(data);
                    }
                );
            }
        };
    }
]);